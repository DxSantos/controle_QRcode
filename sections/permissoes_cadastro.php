<?php

// Incluindo a conexão/configuração
require_once __DIR__ . '/../config/config.php';

// Incluindo arquivos de controle e layout
require_once __DIR__ . '/../includes/verifica_permissao.php';
include_once __DIR__ . '/../includes/header.php';

/** @var PDO $pdo */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// 🔒 Bloqueia se o usuário não tiver permissão "permissoes"
if (!verificaPermissao('permissoes')) {
    echo "<div class='alert alert-danger m-4 text-center'>
            🚫 Você não tem permissão para acessar esta página.
          </div>";
    include 'includes/footer.php';
    exit;
}

// =========================
// EDITAR REGISTRO
// =========================
$edit = false;
$nome_edit = '';
$chave_edit = '';

if (isset($_GET['editar'])) {
    $id = (int) $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM permissoes WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        $edit = true;
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);
        $nome_edit = $dados['nome'];
        $chave_edit = $dados['chave'];
    }
}

// =========================
// EXCLUIR REGISTRO
// =========================
if (isset($_GET['excluir'])) {
    $id = (int) $_GET['excluir'];
    $stmt = $pdo->prepare("DELETE FROM permissoes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: permissoes_midia_QRcodes.php");
    exit;
}

// PESQUISA
// =========================
$busca = isset($_GET['busca']) ? strtoupper(trim($_GET['busca'])) : '';
$termo = "%$busca%";

$sql = "SELECT id, nome, chave
        FROM permissoes
        WHERE UPPER(nome) LIKE ? OR UPPER(chave) LIKE ?
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$termo, $termo]); // Passa o termo para a 1ª e 2ª interrogação
$permissoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container py-4 main-container">
    <form method="POST" action="permissoes_salvar.php" class="mb-4">
        <h4 class="mb-4"><?= $edit ? 'Editar Permissão' : 'Cadastro de Permissões' ?></h4>
        <input type="hidden" name="id" value="<?= $edit ? $dados['id'] : '' ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Nome da Permissão:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($nome_edit) ?>" class="form-control text-capitalize" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Chave (identificador interno):</label>
                <input type="text" name="chave" value="<?= htmlspecialchars($chave_edit) ?>" class="form-control text-lowercase" required>
                <small class="text-muted">Exemplo: <code>produtos</code>, <code>relatorios</code>, <code>vendas</code></small>
            </div>
        </div>

        <button type="submit" class="btn btn-success"><?= $edit ? 'Atualizar' : 'Salvar' ?></button>
        <?php if ($edit): ?>
            <a href="permissoes_midia_QRcodes.php" class="btn btn-secondary">Cancelar</a>
        <?php endif; ?>
    </form>

    <div class="listagem">
        <h4>Lista de Permissões</h4>

        <form method="GET" class="d-flex gap-2 mb-3">
            <input type="text" name="busca" class="form-control" placeholder="Pesquisar por nome ou chave..." value="<?= htmlspecialchars($busca) ?>">
            <button class="btn btn-primary" type="submit">Buscar</button>
            <a href="permissoes_midia_QRcodes.php" class="btn btn-secondary">Limpar</a>
        </form>

        <div class="scrollable-table" style="max-height: 300px; overflow-y: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Chave</th>
                        <th style="width: 150px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($permissoes as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td class="text-capitalize"><?= htmlspecialchars($row['nome']) ?></td>
                            <td><code><?= htmlspecialchars($row['chave']) ?></code></td>
                            <td>
                                <a href="?editar=<?= $row['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                                <a href="?excluir=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja realmente excluir esta permissão?')">🗑️</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>