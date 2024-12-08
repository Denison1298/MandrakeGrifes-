<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

include '../config/conexao.php';

// Verifica a conexão com o banco de dados
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Caso o formulário tenha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : null;  // Torna a descrição opcional
    $preco = $_POST['preco'];

    // Processar tamanhos e cores múltiplos
    $tamanho = isset($_POST['tamanho']) ? trim($_POST['tamanho']) : null;  // Ex.: "P,M,G"
    $cor = isset($_POST['cor']) ? trim($_POST['cor']) : null;              // Ex.: "Vermelho,Azul,Verde"

    // Removido categoria pois estamos usando aba_id
    $aba_id = $_POST['aba_id'];
    $imagem = $_FILES['imagem']['name'];

    // Verificação de imagem
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
    $extensao = strtolower(pathinfo($imagem, PATHINFO_EXTENSION));

    if (!in_array($extensao, $tipos_permitidos)) {
        $_SESSION['msg'] = 'Tipo de arquivo não permitido. Por favor, envie uma imagem com extensão JPG, PNG ou GIF.';
        header("Location: painel.php"); // Redireciona para o painel de administração
        exit();
    }

    // Limitar o tamanho da imagem (por exemplo, 5MB)
    if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
        $_SESSION['msg'] = 'O arquivo é muito grande. O tamanho máximo permitido é 5MB.';
        header("Location: painel.php");
        exit();
    }

    // Diretório para onde a imagem será movida
    $diretorio_imagens = '../imagens_produtos/';
    $caminho_imagem = $diretorio_imagens . $imagem;

    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_imagem)) {
        // Adiciona a URL completa à imagem
        $imagem_completa = 'https://mandrakegrifes.com.br/imagens_produtos/' . $imagem;

        // Inserir o novo produto no banco de dados
        $queryInsert = $conn->prepare(
            "INSERT INTO produtos (nome, descricao, preco, tamanho, cor, aba_id, imagem) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $queryInsert->bind_param("ssdssss", $nome, $descricao, $preco, $tamanho, $cor, $aba_id, $imagem_completa);

        if ($queryInsert->execute()) {
            $_SESSION['msg'] = 'Produto adicionado com sucesso!';
            header("Location: painel.php"); // Redireciona para o painel de administração
            exit();
        } else {
            $_SESSION['msg'] = 'Erro ao adicionar produto: ' . $queryInsert->error;
            header("Location: painel.php"); // Redireciona para o painel de administração
            exit();
        }
    } else {
        $_SESSION['msg'] = 'Erro ao fazer upload da imagem.';
        header("Location: painel.php");
        exit();
    }
}

// Buscar todas as abas para o seletor de abas
$queryAbas = $conn->query("SELECT id, nome_aba FROM abas");

// Inicializa o array de abas
$abas = [];
if ($queryAbas) {
    while ($aba = $queryAbas->fetch_assoc()) {
        $abas[] = $aba;
    }
} else {
    echo "<p>Erro ao buscar abas: " . $conn->error . "</p>";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto</title>
    <link rel="stylesheet" href="../config/admin.css"> <!-- Caminho para o CSS -->
</head>
<body>
    <h1>Adicionar Novo Produto</h1>

    <!-- Botões de Voltar e Logout -->
    <div class="buttons">
        <a href="painel.php" class="btn-voltar">Voltar</a>
        <a href="../config/logout.php" class="btn-logout">Logout</a>
    </div>

    <?php
    // Exibe a mensagem de sucesso ou erro
    if (isset($_SESSION['msg'])) {
        echo "<p>{$_SESSION['msg']}</p>";
        unset($_SESSION['msg']); // Limpa a mensagem após exibição
    }
    ?>

    <form action="adicionar_produto.php" method="POST" enctype="multipart/form-data">
        <label for="nome">Nome do Produto:</label>
        <input type="text" name="nome" required><br>

        <label for="descricao">Descrição (Opcional):</label>
        <textarea name="descricao"></textarea><br>

        <label for="preco">Preço:</label>
        <input type="number" name="preco" step="0.01" required><br>

        <!-- Campo para múltiplos tamanhos -->
        <label for="tamanho">Tamanhos (Opcional):</label>
        <input type="text" name="tamanho" placeholder="Ex.: P,M,G"><br>
        <small>Separe os tamanhos com vírgulas.</small><br>

        <!-- Campo para múltiplas cores -->
        <label for="cor">Cores (Opcional):</label>
        <input type="text" name="cor" placeholder="Ex.: Vermelho,Azul,Verde"><br>
        <small>Separe as cores com vírgulas.</small><br>

        <label for="aba_id">Aba:</label>
        <select name="aba_id" required>
            <option value="">Selecione uma Aba</option>
            <?php
            if (!empty($abas)) {
                foreach ($abas as $aba) {
                    echo "<option value='{$aba['id']}'>{$aba['nome_aba']}</option>";
                }
            } else {
                echo "<option value=''>Nenhuma aba disponível</option>";
            }
            ?>
        </select><br>

        <label for="imagem">Imagem:</label>
        <input type="file" name="imagem" required><br>

        <button type="submit">Adicionar Produto</button>
    </form>
</body>
</html>
