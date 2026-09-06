<?php
// Incluindo a conexão/configuração
require_once __DIR__ . '/../config/config.php';

$dados = $pdo->query("SELECT codigo_qr FROM subgrupos")->fetchAll();

echo "<h1>QR Codes</h1>";

foreach($dados as $d){
    echo "<div style='margin-bottom:20px;text-align:center'>";
    echo "<img src='qrcodes/".$d['codigo_qr'].".png'><br>";
    echo $d['codigo_qr'];
    echo "</div>";
}
?>