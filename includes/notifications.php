<?php
// Small include to show approved-applications as notifications (bell next to logout)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) return;

// hanya tampilkan bell untuk role = 'user'
if ($_SESSION['role'] !== 'user') return;

require_once __DIR__ . '/../config/database.php';
$user_id = (int) $_SESSION['user_id'];
$preview = [];
$unread_count = 0;

// pastikan tabel ada
$tbl = mysqli_query($conn, "SHOW TABLES LIKE 'notifications'");
$hasUserIdCol = false;
if ($tbl && mysqli_num_rows($tbl) > 0) {
    $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'user_id'");
    if ($colCheck && mysqli_num_rows($colCheck) > 0) $hasUserIdCol = true;

    $stmtSql = $hasUserIdCol
        ? "SELECT id, title, message, is_read, created_at, application_id FROM notifications WHERE (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ? OR user_id = ?) ORDER BY created_at DESC LIMIT 5"
        : "SELECT id, title, message, is_read, created_at, application_id FROM notifications WHERE (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ?) ORDER BY created_at DESC LIMIT 5";

    $stmt = $conn->prepare($stmtSql);
    if ($hasUserIdCol) {
        $stmt->bind_param('ii', $user_id, $user_id);
    } else {
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $res = method_exists($stmt, 'get_result') ? $stmt->get_result() : null;
    $preview = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    $cstmtSql = $hasUserIdCol
        ? "SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ? OR user_id = ?)"
        : "SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND (recipient_role = 'all' OR recipient_role = 'user' OR recipient_user_id = ?)";
    $cstmt = $conn->prepare($cstmtSql);
    if ($hasUserIdCol) {
        $cstmt->bind_param('ii', $user_id, $user_id);
    } else {
        $cstmt->bind_param('i', $user_id);
    }
    $cstmt->execute();
    $cres = method_exists($cstmt, 'get_result') ? $cstmt->get_result() : null;
    if ($cres) {
        $row = $cres->fetch_row();
        $unread_count = (int)($row[0] ?? 0);
    }
    $cstmt->close();

    // Normalize message untuk notifikasi jadwal interview agar sama dengan email/admin
    foreach ($preview as &$n) {
        $title = $n['title'] ?? '';
        $appId = (int)($n['application_id'] ?? 0);
        $isScheduleNotif = $appId > 0 && stripos($title, 'jadwal interview') !== false;

        if ($isScheduleNotif) {
            $stmtApp = $conn->prepare("SELECT interview_date, interview_time, interview_location, interview_notes FROM applications WHERE id = ? LIMIT 1");
            if ($stmtApp) {
                $stmtApp->bind_param('i', $appId);
                $stmtApp->execute();
                $resApp = method_exists($stmtApp, 'get_result') ? $stmtApp->get_result() : null;
                if ($resApp && ($rowApp = $resApp->fetch_assoc())) {
                    $datePart = !empty($rowApp['interview_date']) ? date('d M Y', strtotime($rowApp['interview_date'])) : '-';
                    $timePart = !empty($rowApp['interview_time']) ? date('H:i', strtotime($rowApp['interview_time'])) : '00:00';
                    $locPart  = !empty($rowApp['interview_location']) ? $rowApp['interview_location'] : '-';
                    $msg = "Jadwal: {$datePart} {$timePart} WIB. Lokasi: {$locPart}.";
                    if (!empty($rowApp['interview_notes'])) {
                        $msg .= " Catatan: " . $rowApp['interview_notes'];
                    }
                    $n['message'] = $msg;
                }
                $stmtApp->close();
            }
        }
    }
    unset($n);
}

// render dropdown (harus disertakan di dalam <ul class="navbar-nav">)
?>
<li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-bell"></i>
    <?php if ($unread_count > 0): ?><span class="badge bg-danger"><?php echo $unread_count; ?></span><?php endif; ?>
  </a>
  <ul class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="notifDropdown" style="min-width:300px;">
    <li class="dropdown-header ps-3 py-2">Notifications</li>
    <?php if (empty($preview)): ?>
      <li class="dropdown-item small text-muted">No new notifications</li>
    <?php else: foreach ($preview as $n): ?>
      <li>
        <?php
          // link goes to mark endpoint which marks notification read then redirects
          $target = '/cvjurnal/user/view_application.php?id=' . (int)($n['application_id'] ?? 0);
          if (empty($n['application_id'])) $target = '/cvjurnal/notifications.php';
          $href = '/cvjurnal/mark_notification.php?nid=' . (int)$n['id'] . '&goto=' . urlencode($target);
        ?>
        <a class="dropdown-item" href="<?php echo $href; ?>">
          <div class="fw-bold small"><?php echo htmlspecialchars($n['title'] ?? 'Notification'); ?></div>
          <div class="small text-muted"><?php echo htmlspecialchars(substr($n['message'] ?? '',0,120)); ?></div>
          <div class="small text-end text-muted"><?php echo htmlspecialchars($n['created_at'] ?? ''); ?></div>
        </a>
      </li>
      <li><hr class="dropdown-divider my-1"></li>
    <?php endforeach; endif; ?>
    <li><a class="dropdown-item text-center" href="/cvjurnal/notifications.php">View all</a></li>
  </ul>
</li>
<?php
//