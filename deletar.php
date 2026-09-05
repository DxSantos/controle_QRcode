<?php
require 'conexao.php';

$id = $_GET['id'];

$pdo->query("DELETE FROM subgrupos WHERE id = $id");

header("Location: listar.php");