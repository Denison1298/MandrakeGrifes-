<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

include '../config/conexao.php';

if (isset($_GET['id'])) {
    $produto_id = $_GET['id'];

    // Buscar o produto pelo ID
    $queryProduto = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
    $queryProduto->bind_param("i", $produto_id);
    $queryProduto->execute();
    $resultadoProduto = $queryProduto->get_result();

    if ($resultadoProduto->num_rows > 0) {
        $produto = $resultadoProduto->fetch_assoc();
    } else {
        echo "<p>Produto não encontrado.</p>";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = $_POST['nome'];
        $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : null;
        $preco = $_POST['preco'];

        // Processar tamanhos e cores múltiplos
        $tamanho = isset($_POST['tamanho']) ? trim($_POST['tamanho']) : null;  // Ex.: "P,M,G"
        $cor = isset($_POST['cor']) ? trim($_POST['cor']) : null;            // Ex.: "Vermelho,Azul,Verde"

        $aba_id = $_POST['aba_id']; // Mantemos aba_id

        // Verificar se foi enviada uma nova imagem
        if ($_FILES['imagem']['name']) {
            $imagem = $_FILES['imagem']['name'];
            $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
            $extensao = strtolower(pathinfo($imagem, PATHINFO_EXTENSION));

            // Verifica se o tipo de imagem é permitido
            if (!in_array($extensao, $tipos_permitidos)) {
                echo "<p>Tipo de arquivo não permitido. Envie uma imagem JPG, PNG ou GIF.</p>";
                exit();
            }

            // Verifica o tamanho da imagem (máximo de 5MB)
            if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
                echo "<p>Arquivo muito grande. O tamanho máximo permitido é 5MB.</p>";
                exit();
            }

            $diretorio_imagens = '../imagens_produtos/';
            $imagem_completa = $diretorio_imagens . $imagem;

            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem_completa)) {
                $imagem_url = 'https://mandrakegrifes.com.br/imagens_produtos/' . $imagem;

                // Atualizar produto com nova imagem
                $queryUpdate = $conn->prepare(
                    "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, tamanho = ?, cor = ?, aba_id = ?, imagem = ? WHERE id = ?"
                );
                $queryUpdate->bind_param("ssdssssi", $nome, $descricao, $preco, $tamanho, $cor, $aba_id, $imagem_url, $produto_id);
            } else {
                echo "<p>Erro ao fazer upload da imagem.</p>";
                exit();
            }
        } else {
            // Atualizar produto sem mudar a imagem
            $queryUpdate = $conn->prepare(
                "UPDATE produtos SET nome = ?, descricao = ?, preco = ?, tamanho = ?, cor = ?, aba_id = ? WHERE id = ?"
            );
            $queryUpdate->bind_param("ssdsssi", $nome, $descricao, $preco, $tamanho, $cor, $aba_id, $produto_id);
        }

        if ($queryUpdate->execute()) {
            // Redirecionar com mensagem de sucesso
            header("Location: painel.php?mensagem=Produto+editado+com+sucesso");
            exit();
        } else {
            echo "<p>Erro ao atualizar produto: " . $queryUpdate->error . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="stylesheet" href="../config/admin.css">
</head>
<body>
    <h1>Editar Produto</h1>
    <form action="editar_produto.php?id=<?php echo $produto['id']; ?>" method="POST" enctype="multipart/form-data">
        <label for="nome">Nome do Produto:</label>
        <input type="text" name="nome" value="<?php echo $produto['nome']; ?>" required><br>

        <label for="descricao">Descrição:</label>
        <textarea name="descricao"><?php echo $produto['descricao']; ?></textarea><br>

        <label for="preco">Preço:</label>
        <input type="number" name="preco" step="0.01" value="<?php echo $produto['preco']; ?>" required><br>

        <!-- Campo de tamanhos múltiplos -->
        <label for="tamanho">Tamanhos:</label>
        <input type="text" name="tamanho" value="<?php echo $produto['tamanho']; ?>" placeholder="Ex.: P,M,G"><br>
        <small>Separe os tamanhos com vírgulas.</small><br>

        <!-- Campo de cores múltiplas -->
        <label for="cor">Cores:</label>
        <input type="text" name="cor" value="<?php echo $produto['cor']; ?>" placeholder="Ex.: Vermelho,Azul,Verde"><br>
        <small>Separe as cores com vírgulas.</small><br>

        <label for="aba_id">Aba:</label>
        <select name="aba_id" required>
            <option value="">Selecione uma Aba</option>
            <?php
            // Buscar todas as abas disponíveis
            $abas = $conn->query("SELECT id, nome_aba FROM abas");
            while ($aba = $abas->fetch_assoc()) {
                $selected = ($aba['id'] == $produto['aba_id']) ? "selected" : "";
                echo "<option value='{$aba['id']}' {$selected}>{$aba['nome_aba']}</option>";
            }
            ?>
        </select><br>

        <!-- Mostrar imagem atual do produto -->
        <label>Imagem Atual:</label><br>
        <img src="<?php echo $produto['imagem']; ?>" alt="Imagem do Produto" style="max-width: 150px;"><br><br>

        <label for="imagem">Nova Imagem:</label>
        <input type="file" name="imagem"><br><br>

        <button type="submit">Salvar Alterações</button>
    </form>
</body>
</html>
