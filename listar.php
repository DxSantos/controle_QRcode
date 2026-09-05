<?php 

require 'conexao.php'; 
include 'header.php';
?>

<script src="https://cdn.tailwindcss.com"></script>

<div class="p-6">

    <h2 class="text-2xl mb-4">QR Codes</h2>

    <table class="w-full bg-white shadow rounded">

        <tr class="bg-gray-200">
            <th>Código</th>
            <th>Tipo</th>
            <th>QR</th>
            <th>Ação</th>
        </tr>

        <?php
        $stmt = $pdo->query("
SELECT s.codigo_qr, m.tipo 
FROM subgrupos s
JOIN midias m ON m.subgrupo_id = s.id
");

        foreach ($stmt as $row):
        ?>

            <tr class="text-center border-t">
                <td><?= $row['codigo_qr'] ?></td>
                <td><?= $row['tipo'] ?></td>
                <td>
                    <img src="qrcodes/<?= $row['codigo_qr'] ?>.png" width="80">
                </td>
                <td>
                    <a href="player.php?codigo=<?= $row['codigo_qr'] ?>" class="text-blue-500">Abrir</a>
                </td>
            </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php include 'footer.php'; ?>