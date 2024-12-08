<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    header("Location: ../config/logout.php");
    exit();
}

include '../config/conexao.php'; // Conexão com o banco de dados

// Verifica se as abas foram selecionadas
if (isset($_POST['abas']) && is_array($_POST['abas']) && !empty($_POST['abas'])) {
    $abasSelecionadas = $_POST['abas'];

    // Validação: Garante que todos os IDs sejam números inteiros
    $idsValidos = array_filter($abasSelecionadas, fn($id) => is_numeric($id) && intval($id) > 0);

    if (!empty($idsValidos)) {
        // Prepara a consulta utilizando prepared statements
        $placeholders = implode(',', array_fill(0, count($idsValidos), '?'));
        $stmt = $conn->prepare("DELETE FROM abas WHERE id IN ($placeholders)");

        // Vincula os parâmetros
        $stmt->bind_param(str_repeat('i', count($idsValidos)), ...$idsValidos);

        // Executa a consulta
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Abas excluídas com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir as abas.', 'error' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhuma aba válida selecionada.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Nenhuma aba selecionada para exclusão.']);
}

$conn->close();
?>
