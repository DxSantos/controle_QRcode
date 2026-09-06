<?php
date_default_timezone_set('America/Sao_Paulo');

// Incluindo conexões e cabeçalho com __DIR__
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/verifica_permissao.php';
include_once __DIR__ . '/../includes/header.php';

// Controle de sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

/** @var PDO $pdo */

if (!verificaPermissao('movimentacao')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include_once __DIR__ . '/../includes/footer.php';
    exit;
}

$permCadastro = verificaPermissao('cadastro_principal');

// ✅ MENSAGEM DE SUCESSO
if (isset($_GET['ok'])) {
    echo "<div class='alert alert-success m-3 text-center'>
            ✅ QR Code criado com sucesso: <strong>" . htmlspecialchars($_GET['ok']) . "</strong>
          </div>";
}

// 🔁 ATIVAR / INATIVAR
if (isset($_GET['inativar'])) {
    $stmt = $pdo->prepare("UPDATE midiaQR SET ativo=0 WHERE id=?");
    $stmt->execute([$_GET['inativar']]);
    header("Location: midia_QRcodes.php");
    exit;
}

if (isset($_GET['ativar'])) {
    $stmt = $pdo->prepare("UPDATE midiaQR SET ativo=1 WHERE id=?");
    $stmt->execute([$_GET['ativar']]);
    header("Location: midia_QRcodes.php");
    exit;
}

// 💾 SALVAR NOVO QR CODE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $codigo = preg_replace('/[^A-Za-z0-9]/', '', $_POST['codigo_qr']);

    if (empty($codigo)) {
        echo "<div class='alert alert-danger m-3'>Digite um código válido!</div>";
        exit;
    }

    // Verificar se o código já existe na tabela renomeada midiaQR
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM midiaQR WHERE codigo_qr=?");
    $stmt->execute([$codigo]);

    if ($stmt->fetchColumn() > 0) {
        echo "<div class='alert alert-danger m-3'>O código <strong>$codigo</strong> já existe!</div>";
        exit;
    }

    // Inserir na tabela midiaQR
    $stmt = $pdo->prepare("INSERT INTO midiaQR (codigo_qr, ativo) VALUES (?, 1)");
    $stmt->execute([$codigo]);

    // Caminho da pasta qrcodes na RAIZ do projeto
    $pasta_qrcodes = __DIR__ . '/../qrcodes/';

    if (!is_dir($pasta_qrcodes)) {
        mkdir($pasta_qrcodes, 0777, true);
    }

    // Rota web até a View do player
    $url = "http://localhost/controle_QRcode/sections/player.php?codigo=" . $codigo;

    // Gerar e salvar imagem do QR Code
    $imagem_qr = file_get_contents("https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url));
    file_put_contents($pasta_qrcodes . $codigo . ".png", $imagem_qr);

    header("Location: midia_QRcodes.php?ok=" . $codigo);
    exit;
}
?>

<div class="container py-4">

    <!-- CARD FORMULÁRIO DE CADASTRO -->
    <div class="card p-4 shadow-sm mb-4">
        <h3 class="mb-3">Cadastrar QR Code</h3>

        <form method="POST" class="d-flex gap-2 align-items-end">
            <div class="flex-grow-1">
                <label class="form-label fw-bold">Código do QR Code:</label>
                <input type="text" name="codigo_qr" class="form-control" placeholder="Ex: qr1, loja01, mesa05" required>
            </div>
            
            <?php if ($permCadastro): ?>
                <button type="submit" class="btn btn-success px-4">Salvar QR</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- CARD LISTAGEM DOS QR CODES -->
    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">QR Codes Criados</h4>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>QR Code</th>
                        <th>Status</th>
                        <th style="width: 120px;">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM midiaQR ORDER BY id DESC");
                    $qrcodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($qrcodes) > 0):
                        foreach ($qrcodes as $row):
                    ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['codigo_qr']) ?></strong></td>

                            <td>
                                <!-- Caminho relativo apontando para a pasta raiz /qrcodes -->
                                <img src="../qrcodes/<?= htmlspecialchars($row['codigo_qr']) ?>.png" 
                                     width="70" 
                                     height="70"
                                     class="img-thumbnail" 
                                     alt="QR Code <?= htmlspecialchars($row['codigo_qr']) ?>">
                            </td>

                            <td>
                                <?php if ($row['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inativo</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <!-- Alternar Ativo/Inativo -->
                                <?php if ($row['ativo']): ?>
                                    <a href="midia_QRcodes.php?inativar=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger w-100">Inativar</a>
                                <?php else: ?>
                                    <a href="midia_QRcodes.php?ativar=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success w-100">Ativar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">Nenhum QR Code cadastrado até o momento.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>