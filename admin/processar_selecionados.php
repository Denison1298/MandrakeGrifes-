<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

include '../config/conexao.php'; // Conexão com o banco de dados

// Verifica se os produtos foram selecionados
if (isset($_POST['produtos']) && !empty($_POST['produtos'])) {
    // Recebe os IDs dos produtos selecionados
    $produtosSelecionados = $_POST['produtos'];

    // Prepara a consulta para excluir os produtos selecionados
    $ids = implode(",", $produtosSelecionados); // Converte o array de IDs para uma string separada por vírgulas
    $queryExcluir = "DELETE FROM produtos WHERE id IN ($ids)";

    if ($conn->query($queryExcluir) === TRUE) {
        // Se a exclusão foi bem-sucedida, redireciona de volta para o painel administrativo
        $_SESSION['msg'] = "Produtos excluídos com sucesso!";
        header("Location: painel.php");
        exit();
    } else {
        // Se houver erro na exclusão
        $_SESSION['msg'] = "Erro ao excluir os produtos: " . $conn->error;
        header("Location: painel.php");
        exit();
    }
} else {
    // Se nenhum produto foi selecionado
    $_SESSION['msg'] = "Nenhum produto foi selecionado.";
    header("Location: painel.php");
    exit();
}

$conn->close();
?>
