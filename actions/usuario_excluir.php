<?php
require_once __DIR__ . '/../config/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: usuarios_lista.php?msg=Usuário inválido");
    exit;
}

// Desativa o usuário
$stmt = $pdo->prepare("UPDATE usuarios SET ativo = 0 WHERE id = ?");
$stmt->execute([$id]);

header("Location: usuarios_lista.php?msg=Usuário desativado com sucesso!");
exit;
?>
