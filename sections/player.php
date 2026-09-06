<?php
// Conexão com o banco via caminho seguro __DIR__
require_once __DIR__ . '/../config/config.php';

$codigo = $_GET['codigo'] ?? '';

if (empty($codigo)) {
    echo "<div style='color:white; background:#121212; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>⚠️ Código do QR Code não informado.</div>";
    exit;
}

// Busca as mídias associadas ao QR Code ativo usando as novas tabelas
$stmt = $pdo->prepare("
    SELECT m.*
    FROM midias m
    JOIN midiaQR s ON s.id = m.midiaQR_id
    WHERE s.codigo_qr = ? AND s.ativo = 1
");
$stmt->execute([$codigo]);

$midias = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$midias) {
    echo "<div style='color:white; background:#121212; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>QR Code inválido ou sem mídias ativas vinculadas.</div>";
    exit;
}

// Sorteia uma mídia cadastrada no QR
$midia = $midias[array_rand($midias)];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Player de Mídia - QR System</title>

<style>
body{
    margin: 0;
    background: #000;
    color: #fff;
    font-family: Arial, sans-serif;
    overflow: hidden;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

video{
    width: 100%;
    height: 100vh;
    object-fit: cover;
}

.image-box{
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100vh;
}

.image-box img{
    max-width: 100%;
    max-height: 100vh;
    object-fit: contain;
}

.audio-box{
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 80vh;
    text-align: center;
}

#controls{
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 15px;
    z-index: 20;
    flex-wrap: wrap;
    justify-content: center;
    background: rgba(0, 0, 0, 0.5);
    padding: 10px 20px;
    border-radius: 30px;
    backdrop-filter: blur(10px);
}

#toggleBtn{
    width: 50px;
    height: 50px;
    border: none;
    border-radius: 50%;
    font-size: 22px;
    background: rgba(255,255,255,0.2);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

#volume{ width: 90px; }
#progress{ width: 180px; }
#time{ font-size: 12px; }

#overlay{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    z-index: 30;
    cursor: pointer;
}
</style>
</head>

<body>

<div id="overlay">🔊 Toque em qualquer lugar para reproduzir</div>

<?php if ($midia['tipo'] == 'audio'): ?>

    <div class="audio-box">
        <h2>Reproduzindo Áudio</h2>
        <audio id="media" autoplay loop>
            <source src="../uploads/<?= htmlspecialchars($midia['arquivo']) ?>">
        </audio>
    </div>

<?php elseif ($midia['tipo'] == 'video'): ?>

    <video id="media" autoplay muted playsinline loop>
        <source src="../uploads/<?= htmlspecialchars($midia['arquivo']) ?>">
    </video>

<?php else: ?>

    <div class="image-box">
        <img src="../uploads/<?= htmlspecialchars($midia['arquivo']) ?>" alt="Imagem vinculada ao QR">
    </div>

<?php endif; ?>

<!-- CONTROLES EXIBIDOS APENAS PARA ÁUDIO E VÍDEO -->
<?php if ($midia['tipo'] == 'audio' || $midia['tipo'] == 'video'): ?>
    <div id="controls">
        <button id="toggleBtn">⏸️</button>
        🔊 <input type="range" id="volume" min="0" max="1" step="0.01">
        📊 <input type="range" id="progress" value="0">
        ⏱️ <span id="time">00:00 / 00:00</span>
    </div>
<?php endif; ?>

<script>
const media = document.getElementById('media');
const btn = document.getElementById('toggleBtn');
const volume = document.getElementById('volume');
const progress = document.getElementById('progress');
const time = document.getElementById('time');
const overlay = document.getElementById('overlay');

if (media) {
    media.play().catch(() => {});
    volume.value = media.volume;

    volume.addEventListener('input', () => { media.volume = volume.value; });

    btn.addEventListener('click', () => {
        if (media.paused) { media.play(); } else { media.pause(); }
    });

    media.addEventListener('play', () => btn.innerHTML = '⏸️');
    media.addEventListener('pause', () => btn.innerHTML = '▶️');

    media.addEventListener('timeupdate', () => {
        if (!isNaN(media.duration)) {
            progress.max = media.duration;
            progress.value = media.currentTime;
            let current = formatTime(media.currentTime);
            let duration = formatTime(media.duration);
            time.innerText = `${current} / ${duration}`;
        }
    });

    progress.addEventListener('input', () => { media.currentTime = progress.value; });

    function formatTime(sec){
        let m = Math.floor(sec / 60) || 0;
        let s = Math.floor(sec % 60) || 0;
        return `${m}:${s.toString().padStart(2,'0')}`;
    }

    media.addEventListener('ended', () => { location.reload(); });
}

document.body.addEventListener('click', () => {
    if (media) {
        media.muted = false;
        media.play();
    }
    if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen().catch(() => {});
    }
    overlay.style.display = 'none';
}, { once: true });
</script>

</body>
</html>