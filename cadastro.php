<?php include 'header.php'; ?>
<?php require 'conexao.php'; ?>

<div class="card">

    <h2>Adicionar Mídia ao QR</h2>

    <form action="salvar.php" method="POST" enctype="multipart/form-data">

        <label>Subgrupo (QR)</label>
        <select name="subgrupo_id" required>
            <option value="" disabled selected>Selecione um QR</option>

            <?php
            $stmt = $pdo->query("SELECT * FROM subgrupos WHERE ativo=1 ORDER BY id DESC");
            foreach ($stmt as $s):
            ?>
                <option value="<?= $s['id'] ?>">
                    QR: <?= $s['codigo_qr'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Tipo</label>
        <select name="grupo">
            <option value="audio">Áudio</option>
            <option value="video">Vídeo</option>
        </select>

        <label>Arquivo</label>
        <input type="file" name="arquivo" required>

        <button type="submit">Salvar</button>

    </form>

</div>

<?php include 'footer.php'; ?>