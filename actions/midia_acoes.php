<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/verifica_permissao.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['usuario_id'])) exit;

// EXCLUIR MÍDIA
if (isset($_GET['excluir_midia'])) {
    $id = (int)$_GET['excluir_midia'];
    $qr_id = (int)$_GET['qr_id'];

    $stmt = $pdo->prepare("SELECT arquivo FROM midias WHERE id = ?");
    $stmt->execute([$id]);
    $midia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($midia) {
        $path = __DIR__ . '/../uploads/' . $midia['arquivo'];
        if (file_exists($path)) unlink($path);

        $stmtDelete = $pdo->prepare("DELETE FROM midias WHERE id = ?");
        $stmtDelete->execute([$id]);
    }

    // Corrigido para apontar para midia_QRcodes.php
    header("Location: ../sections/midia_QRcodes.php?midiaQR_id=" . $qr_id);
    exit;
}

// EDITAR MÍDIA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_midia'])) {
    $id = (int)$_POST['midia_id'];
    $qr_id = (int)$_POST['qr_id'];
    $novo_tipo = $_POST['tipo'];

    $stmt = $pdo->prepare("UPDATE midias SET tipo = ? WHERE id = ?");
    $stmt->execute([$novo_tipo, $id]);

    // Corrigido para apontar para midia_QRcodes.php
    header("Location: ../sections/midia_QRcodes.php?midiaQR_id=" . $qr_id);
    exit;
}