<?php
// Incluindo a conexão/configuração
require_once __DIR__ . '/../config/config.php';

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../sections/midia_QRcodes.php");
    exit;
}

// Aceita o id via 'midiaQR_id' ou 'subgrupo_id' para manter compatibilidade
$midiaQR_id = $_POST['midiaQR_id'] ?? $_POST['subgrupo_id'] ?? '';
$tipo = $_POST['grupo'] ?? '';

// Validar campos obrigatórios
if (empty($midiaQR_id) || empty($tipo)) {
    echo "<div style='color:red; font-family:sans-serif; padding:20px;'>Preencha todos os campos!</div>";
    exit;
}

if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== 0) {
    echo "<div style='color:red; font-family:sans-serif; padding:20px;'>Erro no upload do arquivo!</div>";
    exit;
}

// Verificar se o QR Code existe e está ativo na tabela midiaQR
$stmt = $pdo->prepare("SELECT ativo FROM midiaQR WHERE id = ?");
$stmt->execute([$midiaQR_id]);
$sub = $stmt->fetch();

if (!$sub || $sub['ativo'] == 0) {
    echo "<div style='color:red; font-family:sans-serif; padding:20px;'>Este QR Code está inativo e não pode receber mídias!</div>";
    exit;
}

// Capturar o nome original do arquivo enviado no formulário
$nome_original = $_FILES['arquivo']['name'];

// Extensões permitidas por tipo de mídia (incluindo imagens)
$permitidos = [
    'audio' => ['mp3', 'wav', 'ogg'],
    'video' => ['mp4', 'webm', 'ogg'],
    'imagem' => ['jpg', 'jpeg', 'png', 'webp', 'gif']
];

$ext = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));

if (!isset($permitidos[$tipo]) || !in_array($ext, $permitidos[$tipo])) {
    echo "<div style='color:red; font-family:sans-serif; padding:20px;'>Extensão de arquivo (.$ext) não permitida para o tipo de mídia selecionado!</div>";
    exit;
}

// Criar pasta de uploads na raiz do projeto, se não existir
$pasta_uploads = __DIR__ . '/../uploads/';
if (!is_dir($pasta_uploads)) {
    mkdir($pasta_uploads, 0777, true);
}

// Salvar arquivo com nome único no servidor para evitar sobrescrever
$arquivo = uniqid() . "." . $ext;
move_uploaded_file($_FILES['arquivo']['tmp_name'], $pasta_uploads . $arquivo);

// Inserir registro na tabela 'midias' salvando o nome do arquivo gerado e o nome_original
$stmt = $pdo->prepare("
    INSERT INTO midias (midiaQR_id, tipo, arquivo, nome_original) 
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$midiaQR_id, $tipo, $arquivo, $nome_original]);

// Redireciona de volta para a view com o QR Code selecionado
header("Location: ../sections/midia_QRcodes.php?midiaQR_id=" . $midiaQR_id);
exit;