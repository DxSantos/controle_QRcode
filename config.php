<?php
$host = 'localhost';
$dbname = 'qr_midias';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Registra o erro detalhado nos logs do servidor
    error_log($e->getMessage()); 
    // Exibe apenas uma mensagem genérica e segura para o usuário
    die("Falha na conexão com o banco de dados."); 
}
?>