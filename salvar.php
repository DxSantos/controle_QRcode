<?php
require 'conexao.php';

// validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cadastro.php");
    exit;
}

// validar campos
if (
    !isset($_POST['subgrupo_id']) || $_POST['subgrupo_id'] == '' ||
    !isset($_POST['grupo']) || $_POST['grupo'] == ''
) {
    echo "Preencha todos os campos!";
    exit;
}

if (!isset($_FILES['arquivo']) || $_FILES['arquivo']['error'] !== 0) {
    echo "Erro no upload do arquivo!";
    exit;
}

$subgrupo_id = $_POST['subgrupo_id'];

// verificar ativo
$stmt = $pdo->prepare("SELECT ativo FROM subgrupos WHERE id=?");
$stmt->execute([$subgrupo_id]);
$sub = $stmt->fetch();

if (!$sub || $sub['ativo'] == 0) {
    echo "Este QR está inativo e não pode ser usado!";
    exit;
}

// validar tipo
$tipo = $_POST['grupo'];

$permitidos = [
    'audio' => ['mp3','wav'],
    'video' => ['mp4','webm']
];

$ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidos[$tipo])) {
    echo "Tipo de arquivo inválido!";
    exit;
}


// criar pasta
if (!is_dir("uploads")) {
    mkdir("uploads", 0777, true);
}

// salvar arquivo
$arquivo = uniqid() . "." . $ext;
move_uploaded_file($_FILES['arquivo']['tmp_name'], "uploads/".$arquivo);



// inserir no banco
$stmt = $pdo->prepare("
INSERT INTO midias (subgrupo_id, tipo, arquivo) 
VALUES (?,?,?)
");
$stmt->execute([$subgrupo_id,$tipo,$arquivo]);

header("Location: cadastro.php");
exit;