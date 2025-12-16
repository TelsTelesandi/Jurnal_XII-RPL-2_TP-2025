<?php
function getPdfUrl($application) {
    if (!empty($application['pdf_path'])) {
        return 'C:/laragon/www/cvjurnal/' . htmlspecialchars($application['pdf_path']);
    }
    return '#';
}

function renderPdfLink($application) {
    $pdfUrl = getPdfUrl($application);
    echo '<a href="' . $pdfUrl . '" class="btn btn-primary btn-sm" target="_blank">View PDF</a>';
}
?>