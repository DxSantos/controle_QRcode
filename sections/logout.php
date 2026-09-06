<?php
session_start();

// Limpa todas as variáveis de sessão armazenadas
$_SESSION = array();

// Se houver cookie de sessão ativo, destrói o cookie no navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Finaliza a sessão do usuário
session_destroy();

// Redireciona via URL relativa a partir da raiz web
header("Location: /controle_QRcode/sections/login.php");
exit;