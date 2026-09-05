<?php
date_default_timezone_set('America/Sao_Paulo');
require 'config.php';
require 'includes/verifica_permissao.php';

// Inicia sessão antes de renderizar qualquer HTML
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona se não estiver logado
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Inclui o header padrão (onde a tag <body> já é aberta)
include 'includes/header.php';

/** @var PDO $pdo */

// Bloqueia se o usuário não tiver permissão "movimentacao"
if (!verificaPermissao('movimentacao')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include 'includes/footer.php';
    exit;
}

// ----- PERMISSÕES DE USUÁRIO BOTÕES -----
$canSaida = verificaPermissao('saidas'); 
$canEntrada = verificaPermissao('entradas'); 
$guardaValores = verificaPermissao('guardar_valores'); 
$salvarBanco = verificaPermissao('salvar_banco'); 

// ----- BUSCA DE DADOS PARA DASHBOARD -----
$totalQR = $pdo->query("SELECT COUNT(*) FROM subgrupos")->fetchColumn();
$totalAudio = $pdo->query("SELECT COUNT(*) FROM midias WHERE tipo='audio'")->fetchColumn();
$totalVideo = $pdo->query("SELECT COUNT(*) FROM midias WHERE tipo='video'")->fetchColumn();
?>

<!-- Container principal usando as classes nativas do seu CSS (sem conflito com Tailwind) -->
<div class="container py-4">

    <h1 class="mb-4">Dashboard</h1>

    <!-- Cards informativos estilizados com o seu CSS / Bootstrap -->
    <div class="row g-4">

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0 rounded-3">
                <h5 class="text-muted">QR Codes</h5>
                <h2 class="display-5 fw-bold mb-0 text-dark"><?= $totalQR ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0 rounded-3">
                <h5 class="text-muted">Áudios</h5>
                <h2 class="display-5 fw-bold mb-0 text-dark"><?= $totalAudio ?></h2>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-4 shadow-sm border-0 rounded-3">
                <h5 class="text-muted">Vídeos</h5>
                <h2 class="display-5 fw-bold mb-0 text-dark"><?= $totalVideo ?></h2>
            </div>
        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>