<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

require '../config/conexao.php'; // Conexão com o banco de dados

// Função para buscar produtos
function buscarProdutos($conn) {
    $stmt = $conn->prepare("SELECT * FROM produtos");
    $stmt->execute();
    return $stmt->get_result();
}

// Função para buscar abas
function buscarAbas($conn) {
    $stmt = $conn->prepare("SELECT * FROM abas");
    $stmt->execute();
    return $stmt->get_result();
}

// Função para buscar usuários
function buscarUsuarios($conn) {
    $stmt = $conn->prepare("SELECT * FROM usuarios");
    $stmt->execute();
    return $stmt->get_result();
}

// Função para verificar a permissão do usuário logado
function verificarPermissao($conn, $user_id) {
    $stmt = $conn->prepare("SELECT permissao FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['permissao'];
    }
    return null;
}

$produtos = buscarProdutos($conn);
$abas = buscarAbas($conn);
$usuarios = buscarUsuarios($conn);

// Verifica a permissão do usuário logado
$usuario_logado_id = $_SESSION['usuario_logado']; // Supondo que o ID do usuário logado está na sessão
$permissao_usuario_logado = verificarPermissao($conn, $usuario_logado_id);

// Função para cadastrar novos usuários (somente admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $permissao_usuario_logado == 'admin') {
    $username = $_POST['username'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Criptografando a senha
    $permissao = $_POST['permissao'];

    $stmt = $conn->prepare("INSERT INTO usuarios (username, senha, permissao) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $senha, $permissao);
    if ($stmt->execute()) {
        $_SESSION['msg'] = 'Usuário cadastrado com sucesso!';
    } else {
        $_SESSION['msg'] = 'Erro ao cadastrar usuário.';
    }
    header("Location: painel.php"); // Redireciona para o painel
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo</title>
    <link rel="icon" href="img\icon.png" type="image/x-icon">
   <link rel="stylesheet" href="../css/admin.css">
    <style>
        .section { display: none; } /* Ocultar seções inicialmente */
        .section.active { display: block; } /* Exibir apenas a seção ativa */
        .navbar { display: flex; justify-content: space-between; }
        .navbar .btn { margin: 0 5px; }
        .color-box {
            width: 20px;
            height: 20px;
            display: inline-block;
            border: 1px solid #000;
            vertical-align: middle;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        img { max-width: 100px; height: auto; }
        .btn { padding: 8px 12px; margin: 5px; text-decoration: none; border: 1px solid #ddd; background-color: #f9f9f9; cursor: pointer; }
        .btn:hover { background-color: #eaeaea; }
        .mensagem {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            color: #333;
        }
        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    
    <h1>Painel Administrativo</h1>

    <!-- Exibir mensagem de feedback -->
    <?php if (!empty($_SESSION['msg'])): ?>
        <p class="mensagem"><?= htmlspecialchars($_SESSION['msg']); ?></p>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <div class="navbar">
        <div class="buttons">
            <a href="adicionar_produto.php" class="btn" aria-label="Adicionar Novo Produto">Adicionar Produto</a>
            <!-- Botões de Gerenciar Abas e Logout ao lado do botão de Adicionar Produto -->
            <button class="btn" id="btnAbas" aria-label="Gerenciar Abas">Gerenciar Abas</button>
            <a href="../config/logout.php" class="btn" aria-label="Logout">Logout</a>
        </div>
    </div>

        <h2>Produtos Existentes</h2>
        <?php if ($produtos->num_rows > 0): ?>
            <form action="processar_selecionados.php" method="POST">
                <div class="buttons">
                    <button type="button" id="selectAllBtn" class="btn" aria-label="Selecionar Todos os Produtos">Selecionar Todos</button>
                    <button type="button" id="deselectAllBtn" class="btn" aria-label="Desmarcar Todos os Produtos">Desmarcar Todos</button>
                    <button type="submit" class="btn" aria-label="Excluir Produtos Selecionados">Excluir Selecionados</button>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllCheckbox" aria-label="Selecionar Todos"></th>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Cor</th>
                            <th>Tamanho</th>
                            <th>Descrição</th>
                            <th>Imagem</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($produto = $produtos->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="produtos[]" value="<?= htmlspecialchars($produto['id']); ?>" aria-label="Selecionar Produto"></td>
                                <td><?= htmlspecialchars($produto['id']); ?></td>
                                <td><?= htmlspecialchars($produto['nome']); ?></td>
                                <td>R$ <?= number_format($produto['preco'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $produto['cor'])): ?>
                                        <span class="color-box" style="background-color: <?= htmlspecialchars($produto['cor']); ?>;"></span>
                                    <?php else: ?>
                                        <?= htmlspecialchars($produto['cor']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($produto['tamanho']); ?></td>
                                <td><?= htmlspecialchars(substr($produto['descricao'], 0, 50)); ?>...</td>
                                <td>
                                    <img src="<?= htmlspecialchars($produto['imagem']); ?>" alt="<?= htmlspecialchars($produto['nome']); ?>">
                                </td>
                                <td>
                                    <a href="editar_produto.php?id=<?= htmlspecialchars($produto['id']); ?>" class="btn" aria-label="Editar Produto">Editar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </form>
        <?php else: ?>
            <p>Não há produtos cadastrados.</p>
        <?php endif; ?>
    </div>

    <!-- Seção de abas -->
    <div class="section" id="abasSection">
        <h2>Gerenciar Abas</h2>
        <form id="formDeletarAbas" action="deletar_abas_selecionadas.php" method="POST">
            <div class="buttons">
                <button type="button" id="selectAllAbas" class="btn" aria-label="Selecionar Todas as Abas">Selecionar Todas</button>
                <button type="button" id="deselectAllAbas" class="btn" aria-label="Desmarcar Todas as Abas">Desmarcar Todas</button>
                <button type="submit" class="btn" aria-label="Excluir Abas Selecionadas">Excluir Selecionadas</button>
            </div>
            <?php if ($abas->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllCheckboxAbas" aria-label="Selecionar Todas as Abas"></th>
                            <th>ID</th>
                            <th>Nome da Aba</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($aba = $abas->fetch_assoc()): ?>
                            <tr>
                                <td><input type="checkbox" name="abas[]" value="<?= htmlspecialchars($aba['id']); ?>" aria-label="Selecionar Aba"></td>
                                <td><?= htmlspecialchars($aba['id']); ?></td>
                                <td><?= htmlspecialchars($aba['nome_aba']); ?></td>
                                <td>
                                    <form action="editar_aba.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($aba['id']); ?>">
                                        <input type="text" name="nome_aba" value="<?= htmlspecialchars($aba['nome_aba']); ?>" required>
                                        <button type="submit" class="btn" aria-label="Salvar Alterações na Aba">Salvar</button>
                                    </form>
                                    <form action="deletar_aba.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($aba['id']); ?>">
                                        <button type="submit" class="btn" aria-label="Excluir Aba">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Não há abas cadastradas.</p>
            <?php endif; ?>
        </form>
    </div>

    <!-- Seção de Cadastro de Usuários (Apenas para Admins) -->
    <?php if ($permissao_usuario_logado == 'admin'): ?>
        <div class="section" id="usuariosSection">
            <h2>Cadastrar Novo Usuário</h2>
            <form action="painel.php" method="POST">
                <div>
                    <label for="username">Nome de Usuário:</label>
                    <input type="text" name="username" required>
                </div>
                <div>
                    <label for="senha">Senha:</label>
                    <input type="password" name="senha" required>
                </div>
                <div>
                    <label for="permissao">Permissão:</label>
                    <select name="permissao">
                        <option value="admin">Admin</option>
                        <option value="user">Usuário</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn" aria-label="Cadastrar Usuário">Cadastrar Usuário</button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="loading" id="loadingIndicator">Carregando...</div>

    <script>
        // Redirecionamento para gerenciar_abas.php
        document.getElementById('btnAbas').addEventListener('click', function() {
            window.location.href = "gerenciar_abas.php";
        });

        document.getElementById('selectAllBtn').addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = true;
            });
        });

        document.getElementById('deselectAllBtn').addEventListener('click', function() {
            document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.checked = false;
            });
        });
    </script>

</body>
</html>
