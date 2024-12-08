<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

include '../config/conexao.php'; // Conexão com o banco de dados

// Buscar todas as abas com Prepared Statements para maior segurança
$queryAbas = "SELECT * FROM abas";
$stmt = $conn->prepare($queryAbas);
$stmt->execute();
$resultAbas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Abas</title>
    <link rel="stylesheet" href="../config/admin.css">
    <link rel="icon" href="img\icon.png" type="image/x-icon">
    <style>
        .section { display: none; }
        .section.active { display: block; }
        .btn { margin: 5px; }
    </style>
</head>
<body>
    <div class="navbar">
        <!-- Botão para voltar ao painel principal -->
        <button class="btn" id="voltarPainel">Voltar para o Painel</button>
    </div>

    <h1>Gerenciar Abas</h1>

    <?php if ($resultAbas->num_rows > 0): ?>
        <!-- Formulário de exclusão de múltiplas abas -->
        <form action="deletar_abas_selecionadas.php" method="POST" id="formDeletar">
            <div class="buttons">
                <button type="button" id="selectAllAbasBtn" class="btn">Selecionar Todas</button>
                <button type="button" id="deselectAllAbasBtn" class="btn">Desmarcar Todas</button>
                <button type="submit" class="btn btn-excluir">Excluir Selecionadas</button>
            </div>

            <table class="abas-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllCheckbox"></th>
                        <th>ID</th>
                        <th>Nome da Aba</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($aba = $resultAbas->fetch_assoc()): ?>
                        <tr>
                            <td><input type="checkbox" name="abas[]" value="<?php echo htmlspecialchars($aba['id'], ENT_QUOTES, 'UTF-8'); ?>"></td>
                            <td><?php echo htmlspecialchars($aba['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($aba['nome_aba'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <!-- Formulário para editar o nome da aba -->
                                <form action="editar_aba.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($aba['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="text" name="nome_aba" value="<?php echo htmlspecialchars($aba['nome_aba'], ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <button type="submit" class="btn btn-editar">Salvar</button>
                                </form>

                                <!-- Formulário para excluir a aba individualmente -->
                                <form action="deletar_aba.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($aba['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <button type="submit" class="btn btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>
    <?php else: ?>
        <p>Não há abas cadastradas.</p>
    <?php endif; ?>

    <script>
        // Voltar para o painel principal
        document.getElementById('voltarPainel').addEventListener('click', function() {
            // Caminho absoluto para o painel
            window.location.href = 'https://mandrakegrifes.com.br/admin/painel.php'; 
        });

        // Selecionar todas as abas
        document.getElementById('selectAllAbasBtn').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="abas[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });

        // Desmarcar todas as abas
        document.getElementById('deselectAllAbasBtn').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="abas[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = false;
            });
        });

        // Marcar/desmarcar todas as abas ao clicar no checkbox principal
        document.getElementById('selectAllCheckbox').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="abas[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });

        // Enviar formulário de exclusão de abas selecionadas
        document.getElementById('formDeletar').addEventListener('submit', function(event) {
            // Verificar se ao menos uma aba foi selecionada
            const checkboxes = document.querySelectorAll('input[name="abas[]"]:checked');
            if (checkboxes.length === 0) {
                alert("Por favor, selecione ao menos uma aba para excluir.");
                event.preventDefault();  // Impedir o envio do formulário
            }
        });
    </script>
</body>
</html>
