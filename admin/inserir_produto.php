<?php
// Ativa a exibição de erros para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

// Conectar ao banco de dados
$conn = new mysqli('localhost', 'mandr615_Denison', '0102@@Deh', 'mandr615_MandrakeGrifes');

// Verifica a conexão com o banco de dados
if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

// Caminho da pasta de destino para as imagens
$pasta_destino = '../imagens_produtos/';

// Verifica se uma imagem foi enviada
if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
    // Obtém a extensão do arquivo
    $extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
    
    // Validação: Verifica se a extensão é permitida
    $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extensao), $tipos_permitidos)) {
        $_SESSION['msg'] = "Erro: Tipo de arquivo não permitido. Envie uma imagem JPG, PNG ou GIF.";
        header("Location: inserir_produto.php");
        exit();
    }

    // Verifica o tamanho do arquivo (5MB máximo)
    if ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
        $_SESSION['msg'] = "Erro: O arquivo é muito grande. O tamanho máximo permitido é 5MB.";
        header("Location: inserir_produto.php");
        exit();
    }

    // Gera um nome único para a imagem
    $novo_nome_arquivo = uniqid() . '.' . $extensao;
    $caminho_completo = $pasta_destino . $novo_nome_arquivo;

    // Move o arquivo para a pasta de destino com o novo nome
    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminho_completo)) {
        // Captura os dados do formulário
        $nome = $_POST['nome'];
        $preco = $_POST['preco'];
        $descricao = $_POST['descricao'];
        $aba_id = $_POST['aba_id'];
        $sub_aba_id = $_POST['sub_aba_id'];

        // Verifica se os campos obrigatórios estão preenchidos
        if (empty($nome) || empty($preco) || empty($descricao) || empty($aba_id)) {
            $_SESSION['msg'] = "Erro: Preencha todos os campos obrigatórios.";
            header("Location: inserir_produto.php");
            exit();
        }

        // Prepara a consulta para inserir o produto no banco de dados (usando bind_param para prevenir SQL Injection)
        $sql = "INSERT INTO produtos (nome, preco, descricao, imagem, aba_id, sub_aba_id) 
                VALUES (?, ?, ?, ?, ?, ?)";

        // Prepara a query
        if ($stmt = $conn->prepare($sql)) {
            // Bind dos parâmetros
            $stmt->bind_param("ssdsii", $nome, $descricao, $preco, $novo_nome_arquivo, $aba_id, $sub_aba_id);
            
            // Executa a query
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Produto adicionado com sucesso!";
                // Limpar variável de sessão após sucesso
                unset($_SESSION['msg']);
            } else {
                $_SESSION['msg'] = "Erro ao adicionar o produto: " . $stmt->error;
            }

            // Fecha a declaração
            $stmt->close();
        } else {
            $_SESSION['msg'] = "Erro na preparação da consulta: " . $conn->error;
        }

        // Redireciona de volta para a página de inserção com a mensagem de sucesso ou erro
        header("Location: inserir_produto.php");
        exit();
    } else {
        $_SESSION['msg'] = "Erro ao mover a imagem para a pasta de destino.";
        header("Location: inserir_produto.php");
        exit();
    }
} else {
    $_SESSION['msg'] = "Erro ao enviar a imagem. Verifique se um arquivo foi selecionado.";
    header("Location: inserir_produto.php");
    exit();
}

// Fecha a conexão
$conn->close();
?>
