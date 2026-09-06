<?php
date_default_timezone_set('America/Sao_Paulo');

// Incluindo conexões e cabeçalho com __DIR__
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/verifica_permissao.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include_once __DIR__ . '/../includes/header.php';

/** @var PDO $pdo */

if (!verificaPermissao('movimentacao')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

// BUSCA TODOS OS QR CODES COM A CONTAGEM INDIVIDUAL DE MÍDIAS
$sql = "SELECT 
            q.id,
            q.codigo_qr,
            q.ativo,
            SUM(CASE WHEN m.tipo = 'audio' THEN 1 ELSE 0 END) AS qtd_audio,
            SUM(CASE WHEN m.tipo = 'video' THEN 1 ELSE 0 END) AS qtd_video,
            SUM(CASE WHEN m.tipo = 'imagem' THEN 1 ELSE 0 END) AS qtd_imagem,
            COUNT(m.id) AS total_midias
        FROM midiaQR q
        LEFT JOIN midias m ON m.midiaQR_id = q.id
        GROUP BY q.id, q.codigo_qr, q.ativo
        ORDER BY q.id DESC";

$qrcodes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ESTILOS EXCLUSIVOS DOS BALÕES EXPANDINDOS NO MODAL -->
<style>
.balloon-card {
    border-radius: 25px;
    padding: 20px;
    color: #fff;
    font-weight: bold;
    box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 130px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.balloon-card:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 12px 20px rgba(0,0,0,0.2);
}

.balloon-audio {
    background: linear-gradient(135deg, #0dcaf0, #0aa2c0);
}

.balloon-video {
    background: linear-gradient(135deg, #0d6efd, #0b5ed7);
}

.balloon-imagem {
    background: linear-gradient(135deg, #198754, #146c43);
}

/* Animação de Balão Expandindo ao abrir o modal */
.modal.fade .modal-dialog-balloon {
    transform: scale(0.3);
    opacity: 0;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.3s ease-in-out;
}

.modal.show .modal-dialog-balloon {
    transform: scale(1);
    opacity: 1;
}

.qr-card-hover {
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.qr-card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.15) !important;
}
</style>

<div class="container py-4">

    <h1 class="mb-4">Dashboard - QR Codes & Mídias</h1>

    <div class="row g-4">
        <?php if (count($qrcodes) > 0): ?>
            <?php foreach ($qrcodes as $qr): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <div class="card p-3 shadow-sm text-center border-0 rounded-3 qr-card-hover" 
                         data-bs-toggle="modal" 
                         data-bs-target="#modalQR<?= $qr['id'] ?>">
                        
                        <!-- Imagem do QR Code -->
                        <img src="../qrcodes/<?= htmlspecialchars($qr['codigo_qr']) ?>.png" 
                             alt="QR Code <?= htmlspecialchars($qr['codigo_qr']) ?>" 
                             class="img-fluid rounded mb-2 border p-1" 
                             style="max-height: 120px; object-fit: contain;">

                        <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($qr['codigo_qr']) ?></h6>
                        <small class="text-muted"><?= $qr['total_midias'] ?> mídia(s)</small>
                    </div>
                </div>

                <!-- 🎈 MODAL EM FORMATO DE BALÕES EXPANDINDOS -->
                <div class="modal fade" id="modalQR<?= $qr['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-balloon modal-lg">
                        <div class="modal-content border-0 rounded-4 shadow-lg">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Detalhes do QR: <?= htmlspecialchars($qr['codigo_qr']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>

                            <div class="modal-body text-center py-4">
                                <p class="text-muted mb-4">Quantidade de arquivos vinculados por tipo de mídia:</p>

                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <!-- BALÃO ÁUDIO -->
                                    <?php if ($qr['qtd_audio'] > 0): ?>
                                        <div class="balloon-card balloon-audio">
                                            <span style="font-size: 2rem;">🎵</span>
                                            <span class="mt-1">Áudio</span>
                                            <span class="fs-3 fw-bolder"><?= $qr['qtd_audio'] ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- BALÃO VÍDEO -->
                                    <?php if ($qr['qtd_video'] > 0): ?>
                                        <div class="balloon-card balloon-video">
                                            <span style="font-size: 2rem;">🎥</span>
                                            <span class="mt-1">Vídeo</span>
                                            <span class="fs-3 fw-bolder"><?= $qr['qtd_video'] ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- BALÃO IMAGEM -->
                                    <?php if ($qr['qtd_imagem'] > 0): ?>
                                        <div class="balloon-card balloon-imagem">
                                            <span style="font-size: 2rem;">🖼️</span>
                                            <span class="mt-1">Imagem</span>
                                            <span class="fs-3 fw-bolder"><?= $qr['qtd_imagem'] ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <!-- MENSAGEM SE NÃO HOUVER MÍDIAS -->
                                    <?php if ($qr['total_midias'] == 0): ?>
                                        <div class="alert alert-secondary mb-0 w-100">
                                            Nenhuma mídia cadastrada neste QR Code ainda.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="modal-footer border-0 justify-content-center pt-0">
                                <a href="midia_QRcodes.php?midiaQR_id=<?= $qr['id'] ?>" class="btn btn-outline-primary px-4">
                                    ⚙️ Gerenciar Mídias
                                </a>
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                    Fechar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">Nenhum QR Code encontrado no sistema.</div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>