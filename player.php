<?php
require 'conexao.php';

$codigo = $_GET['codigo'] ?? '';

// buscar mídias do QR ativo
$stmt = $pdo->prepare("
SELECT m.*
FROM midias m
JOIN subgrupos s ON s.id = m.subgrupo_id
WHERE s.codigo_qr = ? AND s.ativo = 1
");
$stmt->execute([$codigo]);

$midias = $stmt->fetchAll();

if (!$midias) {
    echo "QR inválido ou sem mídias.";
    exit;
}

// escolher aleatório
$midia = $midias[array_rand($midias)];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    margin:0;
    background:black;
    color:white;
    font-family:Arial;
    overflow:hidden;
}

/* VIDEO */
video{
    width:100%;
    height:100vh;
    object-fit:cover;
}

/* AUDIO */
.audio-box{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    height:80vh;
    text-align:center;
}

/* CONTROLES */
#controls{
    position:fixed;
    bottom:20px;
    left:50%;
    transform:translateX(-50%);
    display:flex;
    align-items:center;
    gap:15px;
    z-index:20;
    flex-wrap:wrap;
    justify-content:center;
}

/* BOTÃO PLAY */
#toggleBtn{
    width:65px;
    height:65px;
    border:none;
    border-radius:50%;
    font-size:26px;
    background:rgba(255,255,255,0.2);
    color:white;
    backdrop-filter: blur(10px);
    cursor:pointer;
}

/* SLIDER VOLUME */
#volume{
    width:100px;
}

/* PROGRESS BAR */
#progress{
    width:200px;
}

/* TIME */
#time{
    font-size:12px;
}

/* OVERLAY */
#overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.6);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:20px;
    z-index:10;
}
</style>
</head>

<body>

<div id="overlay">🔊 Toque para ativar o som</div>

<?php if($midia['tipo'] == 'audio'): ?>

<div class="audio-box">
    <h2>Reproduzindo áudio</h2>

    <audio id="media" autoplay loop>
        <source src="uploads/<?= $midia['arquivo'] ?>">
    </audio>
</div>

<?php else: ?>

<video id="media" autoplay muted playsinline loop>
    <source src="uploads/<?= $midia['arquivo'] ?>">
</video>

<?php endif; ?>

<!-- CONTROLES -->
<div id="controls">

    <button id="toggleBtn">⏸️</button>

    🔊 <input type="range" id="volume" min="0" max="1" step="0.01">

    📊 <input type="range" id="progress" value="0">

    ⏱️ <span id="time">00:00 / 00:00</span>

</div>

<script>
const media = document.getElementById('media');
const btn = document.getElementById('toggleBtn');
const volume = document.getElementById('volume');
const progress = document.getElementById('progress');
const time = document.getElementById('time');
const overlay = document.getElementById('overlay');

// autoplay
media.play().catch(() => {});

// 🔊 volume inicial
volume.value = media.volume;

// 🔊 controle volume
volume.addEventListener('input', () => {
    media.volume = volume.value;
});

// ▶️ / ⏸️ play pause
btn.addEventListener('click', () => {
    if (media.paused) {
        media.play();
    } else {
        media.pause();
    }
});

// ícone automático
media.addEventListener('play', () => btn.innerHTML = '⏸️');
media.addEventListener('pause', () => btn.innerHTML = '▶️');

// 📊 progresso
media.addEventListener('timeupdate', () => {
    progress.max = media.duration;
    progress.value = media.currentTime;

    let current = formatTime(media.currentTime);
    let duration = formatTime(media.duration);
    time.innerText = `${current} / ${duration}`;
});

// arrastar progresso
progress.addEventListener('input', () => {
    media.currentTime = progress.value;
});

// ⏱️ formatar tempo
function formatTime(sec){
    let m = Math.floor(sec / 60) || 0;
    let s = Math.floor(sec % 60) || 0;
    return `${m}:${s.toString().padStart(2,'0')}`;
}

// 🔁 repetir automático (já garantido com loop)

// ⏭️ próxima mídia automática
media.addEventListener('ended', () => {
    location.reload();
});

// 🔊 ativar som + fullscreen no clique
document.body.addEventListener('click', () => {

    media.muted = false;

    if (media.requestFullscreen) {
        media.requestFullscreen();
    }

    media.play();

    overlay.style.display = 'none';

}, { once: true });
</script>

</body>
</html>