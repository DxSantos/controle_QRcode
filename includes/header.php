<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se não estiver logado, redireciona
if (empty($_SESSION['usuario_id'])) {
    header('Location: sections/login.php');
    exit;
}

require_once __DIR__ . '/../config/config.php';

// Nome do usuário logado
$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';

// Verifica se o usuário logado é administrador
$stmt = $pdo->prepare("
    SELECT p.nome AS perfil_nome
    FROM usuarios u
    JOIN perfis p ON p.id = u.perfil_id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['usuario_id']]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);
$isAdmin = ($perfil && strtolower($perfil['perfil_nome']) === 'administrador');

// Página atual para destacar
$pagina_atual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/images/icons/favicon.ico">
    <title>Controle de QRcodes</title>
    
    <!-- CSS Bootstrap e Ícones -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS do Projeto -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="d-flex flex-column min-vh-100" style="padding-top: 70px;"> 

    <!-- Navbar Fixa com Z-Index Alto -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow" style="z-index: 1030;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="form_principal.php">
                <i class="bi bi-box-seam"></i> Controle de QRcodes
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Cadastros -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($pagina_atual, ['midia_QRcodes.php']) ? 'active' : '' ?>"
                            href="#" id="menuCadastro" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Cadastros
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="menuCadastro">
                            <li><a class="dropdown-item" href="cadastro_QR.php">Cadastro de QR Codes</a></li>
                        </ul>
                    </li>

                    <!-- Movimentações -->
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'form_principal.php' ? 'active' : '' ?>" href="form_principal.php">Movimentações</a>
                    </li>

                    <!-- Mídia QRcodes -->
                    <li class="nav-item">
                        <a class="nav-link <?= $pagina_atual === 'midia_QRcodes.php' ? 'active' : '' ?>" href="midia_QRcodes.php">Mídia QRcodes</a>
                    </li>

                    <!-- Relatórios -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuRelatorios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Relatórios
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="menuRelatorios">
                            <li><a class="dropdown-item" href="#">Relatório Geral</a></li>
                        </ul>
                    </li>

                    <!-- Administração -->
                    <?php if ($isAdmin): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= in_array($pagina_atual, ['usuarios_lista.php', 'permissoes_usuario.php', 'permissoes_midia_QRcodes.php']) ? 'active' : '' ?>"
                                href="#" id="menuUsuarios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Administração
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="menuUsuarios">
                                <li><a class="dropdown-item" href="usuarios_lista.php">Usuários</a></li>
                                <li><a class="dropdown-item" href="permissoes_usuario.php">Permissões de Usuário</a></li>
                                <li><a class="dropdown-item" href="permissoes_midia_QRcodes.php">Permissões de Cadastro</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Usuário logado -->
                <div class="d-flex align-items-center text-white">
                    <i class="bi bi-person-circle fs-5 me-2"></i>
                    <span class="me-3"><?= htmlspecialchars($nomeUsuario) ?></span>
                    <a href="/controle_QRcode/sections/logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>