<?php
// Ativar exibição de erros para depuração (remover em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir a conexão com o banco de dados
include('../config/conexao.php'); // Caminho correto para o arquivo de conexão

// Verificar se o ID do produto foi enviado via GET
if (isset($_GET['id'])) {
    $id_produto = intval($_GET['id']); // Sanitizar o ID do produto

    // Verificar se o ID é válido
    if ($id_produto <= 0) {
        die("ID do produto inválido.");
    }

    // Query para excluir o produto
    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro ao preparar a consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $id_produto);

    if ($stmt->execute()) {
        // Redirecionar para o painel com a mensagem de sucesso
        header("Location: painel.php?mensagem=Produto+excluído+com+sucesso");
        exit();
    } else {
        // Mostrar erro específico
        die("Erro ao excluir o produto: " . $stmt->error);
    }

    $stmt->close();
} else {
    // Caso o ID não seja fornecido, exibe mensagem de erro
    die("ID do produto não fornecido.");
}

// Fechar conexão
$conn->close();
?>
