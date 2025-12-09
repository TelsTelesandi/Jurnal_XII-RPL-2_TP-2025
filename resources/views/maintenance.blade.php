<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Situs Sedang Dalam Perawatan</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #fff;
      background: linear-gradient(135deg, #003f72, #0077b6, #00b4d8);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
      overflow: hidden;
      padding: 0 20px;
      position: relative;
    }

    .container {
      z-index: 2;
      max-width: 700px;
      position: relative;
    }

    h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
      animation: fadeInDown 1.2s ease;
    }

    .subtitle {
      font-size: 1.3rem;
      font-weight: 500;
      margin-bottom: 20px;
      color: #e0f2ff;
      animation: fadeInDown 1.5s ease;
    }

    p {
      font-size: 1.1rem;
      line-height: 1.8;
      text-align: center;
      animation: fadeInUp 1.5s ease;
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Wave animation */
    .wave path {
      animation: waveMove 10s linear infinite;
    }
    @keyframes waveMove {
      from { transform: translateX(0); }
      to { transform: translateX(-50%); }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>🚧 Situs Sedang Dalam Perawatan</h1>
    <div class="subtitle"> Kami sedang melakukan perbaikan</div>
    <p>
      PT Rekan Kinerja Abadi sedang meningkatkan sistem untuk memberikan layanan terbaik kepada Anda. Silakan kembali beberapa saat lagi. Terima kasih atas pengertiannya.
    </p>
  </div>

  <!-- Wave Layer -->
  <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 200" preserveAspectRatio="none"
       style="position:absolute;bottom:0;left:0;width:200%;height:120px;">
    <path d="M0,80 C150,120 350,40 600,80 C850,120 1050,40 1200,80 L1200,200 L0,200 Z" fill="rgba(255,255,255,0.25)" />
  </svg>
  <svg class="wave" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 200" preserveAspectRatio="none"
       style="position:absolute;bottom:0;left:0;width:200%;height:140px;">
    <path d="M0,100 C200,160 400,20 700,100 C1000,180 1100,40 1200,100 L1200,200 L0,200 Z" fill="rgba(255,255,255,0.15)" />
  </svg>
</body>
</html>
