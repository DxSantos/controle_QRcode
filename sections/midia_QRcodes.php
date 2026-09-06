<?php
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/verifica_permissao.php';
include_once __DIR__ . '/../includes/header.php';

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

// QR Selecionado via GET ou padrão
$midiaQR_id = isset($_GET['midiaQR_id']) ? (int)$_GET['midiaQR_id'] : 0;
?>

<div class="container py-4">

    <!-- FORMULÁRIO DE CADASTRO -->
    <div class="card p-4 shadow-sm mb-4">
        <h3 class="mb-3">Adicionar Mídia ao QR</h3>

        <form action="../actions/salvar.php" method="POST" enctype="multipart/form-data">

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">QR Code</label>
                    <select name="midiaQR_id" id="selectQR" class="form-select" required onchange="filtrarMidias(this.value)">
                        <option value="" disabled <?= $midiaQR_id == 0 ? 'selected' : '' ?>>Selecione um QR Code</option>

                        <?php
                        $stmt = $pdo->query("SELECT * FROM midiaQR WHERE ativo=1 ORDER BY id DESC");
                        foreach ($stmt as $s):
                        ?>
                            <option value="<?= $s['id'] ?>" <?= $midiaQR_id == $s['id'] ? 'selected' : '' ?>>
                                QR: <?= htmlspecialchars($s['codigo_qr']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Tipo de Mídia</label>
                    <select name="grupo" id="selectTipoMidia" class="form-select" required onchange="toggleCampoLetra(this.value)">
                        <option value="audio">Áudio</option>
                        <option value="video">Vídeo</option>
                        <option value="imagem">Imagem</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Arquivo</label>
                    <input type="file" name="arquivo" class="form-control" required>
                </div>

                <!-- CAMPO OPCIONAL PARA LETRA DO ÁUDIO -->
                <div class="col-12" id="campoLetraAudio">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-bold mb-0">Letra do Áudio <small class="text-muted fw-normal">(Opcional)</small></label>

                        <!-- Botão para disparar a busca automática -->
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="buscarLetraAutomatica()">
                            🔍 Buscar Letra na Web
                        </button>
                    </div>

                    <textarea style="height: max-content;" name="letra_audio" id="inputLetraAudio" class="form-control" rows="4" placeholder="Digite ou clique no botão acima para buscar a letra automaticamente..."></textarea>
                    <div id="statusBuscaLetra" class="form-text"></div>
                </div>
            </div>

            <div class="mt-4">
                <?php if ($permCadastro): ?>
                    <button type="submit" class="btn btn-outline-success px-4">Salvar no Banco</button>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- LISTAGEM DE MÍDIAS DO QR SELECIONADO -->
    <div class="card p-4 shadow-sm">
        <h4 class="mb-3">Mídias Vinculadas</h4>

        <?php if ($midiaQR_id > 0): ?>
            <?php
            $stmtMidias = $pdo->prepare("SELECT * FROM midias WHERE midiaQR_id = ? ORDER BY id DESC");
            $stmtMidias->execute([$midiaQR_id]);
            $listMidias = $stmtMidias->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (count($listMidias) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tipo</th>
                                <th>Nome Original</th>
                                <th>Letra</th>
                                <th>Preview</th>
                                <th style="width: 200px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listMidias as $m): ?>
                                <tr>
                                    <td>
                                        <?php if ($m['tipo'] == 'audio'): ?>
                                            <span class="badge bg-info">Áudio</span>
                                        <?php elseif ($m['tipo'] == 'video'): ?>
                                            <span class="badge bg-primary">Vídeo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Imagem</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($m['nome_original'] ?? $m['arquivo']) ?></strong>
                                    </td>
                                    <td>
                                        <!-- EXIBIÇÃO / VISUALIZAÇÃO DA LETRA -->
                                        <?php if ($m['tipo'] == 'audio' && !empty($m['letra_audio'])): ?>
                                            <button class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalLetra<?= $m['id'] ?>">
                                                📄 Ver Letra
                                            </button>
                                        <?php elseif ($m['tipo'] == 'audio'): ?>
                                            <span class="text-muted small">Sem letra</span>
                                        <?php else: ?>
                                            <span class="text-muted small">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- PRÉVIA DIRETA CLICÁVEL QUE DISPARA O MODAL -->
                                        <div class="preview-trigger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPreview<?= $m['id'] ?>"
                                            style="cursor: pointer;"
                                            title="Clique para ampliar/reproduzir">

                                            <?php if ($m['tipo'] == 'audio'): ?>
                                                <audio controls style="max-width: 220px; height: 35px; pointer-events: none;">
                                                    <source src="../uploads/<?= htmlspecialchars($m['arquivo']) ?>">
                                                </audio>
                                            <?php elseif ($m['tipo'] == 'video'): ?>
                                                <video width="120" height="70" style="pointer-events: none;" class="rounded border">
                                                    <source src="../uploads/<?= htmlspecialchars($m['arquivo']) ?>">
                                                </video>
                                            <?php else: ?>
                                                <img src="../uploads/<?= htmlspecialchars($m['arquivo']) ?>"
                                                    alt="Preview"
                                                    width="80"
                                                    height="60"
                                                    class="img-thumbnail"
                                                    style="object-fit: cover;">
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2" style="gap: 10px;">
                                            <!-- Botão Modal Editar -->
                                            <button class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEdit<?= $m['id'] ?>">✏️ Editar</button>

                                            <!-- Link Excluir -->
                                            <a href="../actions/midia_acoes.php?excluir_midia=<?= $m['id'] ?>&qr_id=<?= $midiaQR_id ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Deseja remover esta mídia?')">🗑️ Excluir</a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- 🔍 MODAL DE PREVIEW DA MÍDIA -->
                                <div class="modal fade modal-preview-midia" id="modalPreview<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Visualização - <?= htmlspecialchars($m['nome_original'] ?? $m['arquivo']) ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                            </div>
                                            <div class="modal-body text-center bg-light">
                                                <?php if ($m['tipo'] == 'audio'): ?>
                                                    <div class="p-4">
                                                        <audio controls class="w-100">
                                                            <source src="../uploads/<?= htmlspecialchars($m['arquivo']) ?>">
                                                        </audio>
                                                    </div>
                                                <?php elseif ($m['tipo'] == 'video'): ?>
                                                    <div class="ratio ratio-16x9">
                                                        <video controls>
                                                            <source src="../uploads/<?= htmlspecialchars($m['arquivo']) ?>">
                                                        </video>
                                                    </div>
                                                <?php else: ?>
                                                    <img src="../uploads/<?= htmlspecialchars($m['arquivo']) ?>"
                                                        alt="Preview do arquivo"
                                                        class="img-fluid rounded shadow-sm"
                                                        style="max-height: 70vh; object-fit: contain;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 📄 MODAL PARA VISUALIZAR A LETRA DO ÁUDIO -->
                                <?php if (!empty($m['letra_audio'])): ?>
                                    <div class="modal fade" id="modalLetra<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Letra: <?= htmlspecialchars($m['nome_original'] ?? $m['arquivo']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="p-3 bg-light border rounded text-start" style="white-space: pre-wrap; max-height: 350px; overflow-y: auto;">
                                                        <?= htmlspecialchars($m['letra_audio']) ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- ✏️ MODAL EDIÇÃO (INCLUI CAMPO DA LETRA) -->
                                <div class="modal fade" id="modalEdit<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="../actions/midia_acoes.php" method="POST">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Editar Mídia</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="editar_midia" value="1">
                                                    <input type="hidden" name="midia_id" value="<?= $m['id'] ?>">
                                                    <input type="hidden" name="qr_id" value="<?= $midiaQR_id ?>">

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Tipo</label>
                                                        <select name="tipo" class="form-select" required>
                                                            <option value="audio" <?= $m['tipo'] == 'audio' ? 'selected' : '' ?>>Áudio</option>
                                                            <option value="video" <?= $m['tipo'] == 'video' ? 'selected' : '' ?>>Vídeo</option>
                                                            <option value="imagem" <?= $m['tipo'] == 'imagem' ? 'selected' : '' ?>>Imagem</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Letra do Áudio <small class="text-muted fw-normal">(Opcional)</small></label>
                                                        <textarea name="letra_audio" class="form-control" rows="4"><?= htmlspecialchars($m['letra_audio'] ?? '') ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer d-flex gap-2" style="gap: 10px;">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-outline-success">Salvar Alterações</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0">Nenhuma mídia vinculada a este QR Code ainda.</div>
            <?php endif; ?>

        <?php else: ?>
            <div class="alert alert-secondary mb-0">Selecione um QR Code no campo acima para visualizar as mídias cadastradas.</div>
        <?php endif; ?>
    </div>

</div>

<script>
    function filtrarMidias(id) {
        if (id) {
            window.location.href = 'midia_QRcodes.php?midiaQR_id=' + id;
        }
    }

    // Alterna a exibição do campo de letra do áudio
    function toggleCampoLetra(tipo) {
        const campo = document.getElementById('campoLetraAudio');
        if (tipo === 'audio') {
            campo.style.display = 'block';
        } else {
            campo.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Inicializa estado do campo de letra no carregamento
        const selectTipo = document.getElementById('selectTipoMidia');
        if (selectTipo) {
            toggleCampoLetra(selectTipo.value);
        }

        // Pausa o áudio/vídeo quando o modal de preview for fechado
        const modalsPreview = document.querySelectorAll('.modal-preview-midia');
        modalsPreview.forEach(function(modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const mediaElements = modal.querySelectorAll('audio, video');
                mediaElements.forEach(function(media) {
                    media.pause();
                });
            });
        });
    });

    async function buscarLetraAutomatica() {
        const fileInput = document.querySelector('input[name="arquivo"]');
        const campoLetra = document.getElementById('inputLetraAudio');
        const statusMsg = document.getElementById('statusBuscaLetra');

        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Por favor, selecione primeiro um arquivo de áudio.');
            return;
        }

        // Pega o nome do arquivo com extensão (ex: "Legiao Urbana - Tempo Perdido.mp3")[cite: 1]
        const nomeArquivo = fileInput.files[0].name;

        statusMsg.innerHTML = '<span class="text-info">🤖 Processando o nome do arquivo e buscando a letra via IA...</span>';

        try {
            const response = await fetch('../actions/buscar_letra_ia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome_arquivo: nomeArquivo
                })
            });

            const data = await response.json();

            if (data.sucesso) {
                campoLetra.value = data.letra;
                statusMsg.innerHTML = '<span class="text-success">✅ Letra encontrada e preenchida com sucesso!</span>';
            } else {
                statusMsg.innerHTML = `<span class="text-warning">⚠️ ${data.mensagem}</span>`;
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
            statusMsg.innerHTML = '<span class="text-danger">❌ Ocorreu um erro ao conectar com o serviço de busca por IA.</span>';
        }
    }
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>