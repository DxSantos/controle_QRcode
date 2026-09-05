<?php include 'header.php'; ?>
<?php require 'conexao.php'; ?>

<?php
// ✅ MENSAGEM DE SUCESSO
if (isset($_GET['ok'])) {
    echo "<p style='color:green; font-weight:bold;'>
    QR criado com sucesso: ".$_GET['ok']."
    </p>";
}

// 🔁 ATIVAR / INATIVAR
if (isset($_GET['inativar'])) {
    $pdo->prepare("UPDATE subgrupos SET ativo=0 WHERE id=?")
        ->execute([$_GET['inativar']]);
    header("Location: subgrupos.php");
    exit;
}

if (isset($_GET['ativar'])) {
    $pdo->prepare("UPDATE subgrupos SET ativo=1 WHERE id=?")
        ->execute([$_GET['ativar']]);
    header("Location: subgrupos.php");
    exit;
}

// 💾 SALVAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $codigo = preg_replace('/[^A-Za-z0-9]/', '', $_POST['codigo_qr']);

    // validar vazio
    if (empty($codigo)) {
        echo "Digite um código!";
        exit;
    }

    // verificar duplicado
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subgrupos WHERE codigo_qr=?");
    $stmt->execute([$codigo]);

    if ($stmt->fetchColumn() > 0) {
        echo "Código já existe!";
        exit;
    }

    // inserir
    $stmt = $pdo->prepare("INSERT INTO subgrupos (codigo_qr, ativo) VALUES (?,1)");
    $stmt->execute([$codigo]);

    // criar pasta se não existir
    if (!is_dir("qrcodes")) {
        mkdir("qrcodes", 0777, true);
    }

    // gerar QR
    //$url = "http://seusite.com/player.php?codigo=" . $codigo;
    $url = "http://localhost/qrcode/player.php?codigo=" . $codigo;

    file_put_contents(
        "qrcodes/" . $codigo . ".png",
        file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . $url)
    );

    header("Location: subgrupos.php?ok=".$codigo);
exit;
}
?>

<h1>Cadastrar QR Code</h1>

<form method="POST">

<p>
<label>Código do QR:</label><br>
<input type="text" name="codigo_qr" required>
</p>

<p>
<button type="submit">Salvar QR</button>
</p>

</form>

<hr>

<h2>QR Codes criados</h2>

<table border="1" cellpadding="10">
<tr>
    <th>Código</th>
    <th>QR</th>
    <th>Status</th>
    <th>Ação</th>
</tr>

<?php
$stmt = $pdo->query("SELECT * FROM subgrupos ORDER BY id DESC");

foreach ($stmt as $row):
?>

<tr>
    <td><?= $row['codigo_qr'] ?></td>

    <td>
        <img src="qrcodes/<?= $row['codigo_qr'] ?>.png" width="90"
style="border:1px solid #ccc; padding:5px; border-radius:6px;">
    </td>

    <td>
        <?= $row['ativo'] ? 'Ativo' : 'Inativo' ?>
    </td>

    <td>
        <?php if ($row['ativo']): ?>
            <a href="subgrupos.php?inativar=<?= $row['id'] ?>">Inativar</a>
        <?php else: ?>
            <a href="subgrupos.php?ativar=<?= $row['id'] ?>">Ativar</a>
        <?php endif; ?>
    </td>
</tr>

<?php endforeach; ?>

</table>

<?php include 'footer.php'; ?>