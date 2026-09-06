<?php
require_once __DIR__ . '/../config/config.php';

// EDITAR MÍDIA E LETRA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_midia'])) {
    $midia_id    = (int)($_POST['midia_id'] ?? 0);
    $qr_id       = (int)($_POST['qr_id'] ?? 0);
    $tipo        = $_POST['tipo'] ?? '';
    $letra_audio = $_POST['letra_audio'] ?? null; // Captura a letra enviada no modal

    if ($midia_id > 0 && !empty($tipo)) {
        // Atualiza o tipo e o campo letra_audio na tabela 'midias'
        $stmt = $pdo->prepare("
            UPDATE midias 
            SET tipo = ?, letra_audio = ? 
            WHERE id = ?
        ");
        $stmt->execute([$tipo, $letra_audio, $midia_id]);
    }

    // Redireciona de volta para a tela de listagem mantendo o QR Code selecionado
    header("Location: ../sections/midia_QRcodes.php?midiaQR_id=" . $qr_id);
    exit;
}

// REMOVER / EXCLUIR MÍDIA
if (isset($_GET['excluir_midia'])) {
    $midia_id = (int)$_GET['excluir_midia'];
    $qr_id    = (int)($_GET['qr_id'] ?? 0);

    if ($midia_id > 0) {
        // (Opcional) Busca o nome do arquivo para remover do disco antes de apagar do banco
        $stmtFile = $pdo->prepare("SELECT arquivo FROM midias WHERE id = ?");
        $stmtFile->execute([$midia_id]);
        $arq = $stmtFile->fetch(PDO::FETCH_ASSOC);

        if ($arq && !empty($arq['arquivo'])) {
            $caminhoArquivo = __DIR__ . '/../uploads/' . $arq['arquivo'];
            if (file_exists($caminhoArquivo)) {
                unlink($caminhoArquivo);
            }
        }

        // Apaga o registro do banco de dados
        $stmt = $pdo->prepare("DELETE FROM midias WHERE id = ?");
        $stmt->execute([$midia_id]);
    }

    header("Location: ../sections/midia_QRcodes.php?midiaQR_id=" . $qr_id);
    exit;
}