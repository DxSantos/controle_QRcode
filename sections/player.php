<?php
// Inicia sessão antes de qualquer saída de texto para guardar histórico de mídias tocadas
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexão com o banco via caminho seguro __DIR__
require_once __DIR__ . '/../config/config.php';

$codigo = $_GET['codigo'] ?? '';

if (empty($codigo)) {
    echo "<div style='color:white; background:#000; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>⚠️ Código do QR Code não informado.</div>";
    exit;
}

// Busca as mídias associadas ao QR Code ativo na tabela 'midiaQR'
$stmt = $pdo->prepare("
    SELECT m.*
    FROM midias m
    JOIN midiaQR s ON s.id = m.midiaQR_id
    WHERE s.codigo_qr = ? AND s.ativo = 1
");
$stmt->execute([$codigo]);

$midias = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$midias) {
    echo "<div style='color:white; background:#000; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>QR Code inválido ou sem mídias ativas vinculadas.</div>";
    exit;
}

// LÓGICA PARA NÃO REPETIR A MÍDIA RECENTEMENTE TOCADA NA MESMA NAVEGAÇÃO
if (!isset($_SESSION['historico_midias'][$codigo])) {
    $_SESSION['historico_midias'][$codigo] = [];
}

$midias_nao_tocadas = array_filter($midias, function ($item) use ($codigo) {
    return !in_array($item['id'], $_SESSION['historico_midias'][$codigo]);
});

if (empty($midias_nao_tocadas)) {
    $_SESSION['historico_midias'][$codigo] = [];
    $midias_nao_tocadas = $midias;
}

$midias_nao_tocadas = array_values($midias_nao_tocadas);
$midia = $midias_nao_tocadas[array_rand($midias_nao_tocadas)];

$_SESSION['historico_midias'][$codigo][] = $midia['id'];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Player de Mídia - QR System</title>

    <style>
        body {
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

        video {
            width: 100%;
            height: 100vh;
            object-fit: cover;
        }

        .image-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100vh;
            overflow: auto;
            touch-action: pinch-zoom;
        }

        .image-box img {
            max-width: 100%;
            max-height: 100vh;
            object-fit: contain;
            touch-action: pinch-zoom;
        }

        .audio-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            width: 100%;
            text-align: center;
        }

        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            cursor: pointer;
        }

        .overlay-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 15px;
        }

        .play-orange-icon {
            width: 90px;
            height: 90px;
            object-fit: contain;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.5));
            animation: pulsePlay 1.8s infinite ease-in-out;
        }

        .overlay-content p {
            color: #ffffff;
            font-size: 18px;
            font-weight: 500;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
        }

        @keyframes pulsePlay {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        #mediaControls {
            position: fixed;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            z-index: 20;
            opacity: 1;
            transition: opacity 0.4s ease-in-out, visibility 0.4s;
            visibility: visible;
        }

        #mediaControls.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .progress-container {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .progress-bar-wrapper {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: #ffffff;
            border-radius: 20px;
            transition: width 0.1s linear;
        }

        .time-row {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 14px;
            font-weight: bold;
            color: #ffffff;
            padding: 0 4px;
        }

        .buttons-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            position: relative;
            width: 100%;
        }

        .btn-ctrl {
            background: rgba(255, 255, 255, 0.35);
            border: none;
            color: white;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
            transition: transform 0.2s, background 0.2s;
        }

        .btn-ctrl:active {
            transform: scale(0.9);
        }

        .btn-small {
            width: 45px;
            height: 45px;
            font-size: 18px;
        }

        .btn-main {
            width: 60px;
            height: 60px;
            font-size: 26px;
            background: rgba(255, 255, 255, 0.5);
        }

        .btn-audio-toggle {
            position: absolute;
            right: 10px;
            background: transparent;
            border: none;
            color: white;
            font-size: 22px;
            cursor: pointer;
        }

        .volume-popup {
            position: absolute;
            right: 5px;
            bottom: 60px;
            background: rgba(30, 30, 30, 0.9);
            padding: 15px 10px;
            border-radius: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            backdrop-filter: blur(10px);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .volume-popup.active {
            opacity: 1;
            visibility: visible;
        }

        .volume-slider-vertical {
            writing-mode: bt-lr;
            appearance: slider-vertical;
            width: 8px;
            height: 100px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div id="overlay">
        <div class="overlay-content">
            <!-- nome original da mídia sem a extensão e letras grandes-->
            <h2 class="mb-4"><?= htmlspecialchars(pathinfo($midia['nome_original'], PATHINFO_FILENAME)) ?></h2>

            <img src="../assets/images/icons/play_laranja.png" alt="Iniciar Play" class="play-orange-icon">
            <p>Toque em qualquer lugar para reproduzir</p>

        </div>
    </div>

    <?php if ($midia['tipo'] == 'audio'): ?>

        <div class="audio-box">
            <!-- nome original da mídia sem a extensão e letras grandes -->
             <p style="font-size: 24px; font-weight: bold;"><?= htmlspecialchars(pathinfo($midia['nome_original'], PATHINFO_FILENAME)) ?></p>

            <h2 class="mb-4">🎵 Reproduzindo Áudio</h2>
            <audio id="media">
                <source src="../uploads/<?= htmlspecialchars($midia['arquivo']) ?>">
            </audio>
        </div>

    <?php elseif ($midia['tipo'] == 'video'): ?>

        <video id="media" playsinline>
            <source src="../uploads/<?= htmlspecialchars($midia['arquivo']) ?>">
        </video>

    <?php else: ?>

        <div class="image-box">
            <img src="../uploads/<?= htmlspecialchars($midia['arquivo']) ?>" alt="Imagem vinculada ao QR">
        </div>

    <?php endif; ?>

    <?php if ($midia['tipo'] == 'audio' || $midia['tipo'] == 'video'): ?>
        <div id="mediaControls">
            <div class="progress-container">
                <div class="progress-bar-wrapper" id="progressWrapper">
                    <div class="progress-bar-fill" id="progressFill"></div>
                </div>
                <div class="time-row">
                    <span id="currentTime">00:00</span>
                    <span id="totalTime">00:00</span>
                </div>
            </div>

            <div class="buttons-row">
                <button class="btn-ctrl btn-small" id="btnRewind" title="Voltar 10s">⏪</button>
                <button class="btn-ctrl btn-main" id="btnPlayPause">▶️</button>
                <button class="btn-ctrl btn-small" id="btnForward" title="Avançar 10s">⏩</button>
                <button class="btn-audio-toggle" id="btnAudioToggle" title="Volume">🔊</button>

                <div class="volume-popup" id="volumePopup">
                    <input type="range" class="volume-slider-vertical" id="volumeSlider" min="0" max="1" step="0.05" value="1">
                    <span>🔊</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const media = document.getElementById('media');
        const overlay = document.getElementById('overlay');
        const hasMedia = <?= json_encode($midia['tipo'] === 'audio' || $midia['tipo'] === 'video') ?>;

        const controls = document.getElementById('mediaControls');
        const btnPlayPause = document.getElementById('btnPlayPause');
        const btnRewind = document.getElementById('btnRewind');
        const btnForward = document.getElementById('btnForward');
        const progressWrapper = document.getElementById('progressWrapper');
        const progressFill = document.getElementById('progressFill');
        const currentTimeEl = document.getElementById('currentTime');
        const totalTimeEl = document.getElementById('totalTime');
        const btnAudioToggle = document.getElementById('btnAudioToggle');
        const volumePopup = document.getElementById('volumePopup');
        const volumeSlider = document.getElementById('volumeSlider');

        let hideTimeout = null;

        // INICIAR REPRODUÇÃO AO CLICAR NA TELA
        document.body.addEventListener('click', () => {
            if (overlay.style.display !== 'none') {
                if (media) {
                    media.muted = false;
                    media.play();
                    if (btnPlayPause) btnPlayPause.innerHTML = '⏸️';
                }
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(() => {});
                }
                overlay.style.display = 'none';
                if (hasMedia) showControls();
            }
        }, {
            once: true
        });

        if (hasMedia && media) {

            function showControls() {
                controls.classList.remove('hidden');
                clearTimeout(hideTimeout);
                hideTimeout = setTimeout(() => {
                    if (!volumePopup.classList.contains('active')) {
                        controls.classList.add('hidden');
                    }
                }, 2000);
            }

            document.body.addEventListener('click', (e) => {
                if (e.target.closest('#mediaControls') || overlay.style.display !== 'none') {
                    return;
                }

                if (controls.classList.contains('hidden')) {
                    showControls();
                } else {
                    controls.classList.add('hidden');
                    volumePopup.classList.remove('active');
                }
            });

            // Play / Pause
            btnPlayPause.addEventListener('click', (e) => {
                e.stopPropagation();
                if (media.paused) {
                    media.play();
                    btnPlayPause.innerHTML = '⏸️';
                } else {
                    media.pause();
                    btnPlayPause.innerHTML = '▶️';
                }
                showControls();
            });

            // Retroceder / Avançar 10s
            btnRewind.addEventListener('click', (e) => {
                e.stopPropagation();
                media.currentTime = Math.max(0, media.currentTime - 10);
                showControls();
            });

            btnForward.addEventListener('click', (e) => {
                e.stopPropagation();
                media.currentTime = Math.min(media.duration, media.currentTime + 10);
                showControls();
            });

            // Atualiza progresso e contadores
            media.addEventListener('timeupdate', () => {
                if (!isNaN(media.duration)) {
                    const pct = (media.currentTime / media.duration) * 100;
                    progressFill.style.width = `${pct}%`;
                    currentTimeEl.innerText = formatTime(media.currentTime);
                    totalTimeEl.innerText = formatTime(media.duration);
                }
            });

            // REINICIA A MÍDIA AUTOMATICAMENTE AO CHEGAR NO FINAL
            media.addEventListener('ended', () => {
                media.currentTime = 0; // Reseta o tempo para o começo
                media.pause(); // Toca novamente
                if (btnPlayPause) btnPlayPause.innerHTML = '▶️';
            });

            // Arrastar/clicar na barra de progresso
            progressWrapper.addEventListener('click', (e) => {
                e.stopPropagation();
                const rect = progressWrapper.getBoundingClientRect();
                const pos = (e.clientX - rect.left) / rect.width;
                media.currentTime = pos * media.duration;
                showControls();
            });

            // Volume e Mute
            btnAudioToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                volumePopup.classList.toggle('active');
                showControls();
            });

            volumeSlider.addEventListener('input', (e) => {
                e.stopPropagation();
                media.volume = volumeSlider.value;
                media.muted = volumeSlider.value == 0;
                btnAudioToggle.innerText = media.muted || volumeSlider.value == 0 ? '🔇' : '🔊';
                showControls();
            });

            function formatTime(sec) {
                let m = Math.floor(sec / 60) || 0;
                let s = Math.floor(sec % 60) || 0;
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            }
        }
    </script>

</body>

</html>