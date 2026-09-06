<?php
// Incluindo a conexão/configuração
require_once __DIR__ . '/../config/config.php';

$id = $_GET['id'];

$pdo->query("DELETE FROM subgrupos WHERE id = $id");

header("Location: listar.php");