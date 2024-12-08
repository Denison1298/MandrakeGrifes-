<?php
include '../config/conexao.php'; // Conex«ªo com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['abas']) && is_array($_POST['abas'])) {
    // Valida e sanitiza os IDs recebidos
    $abas = array_filter($_POST['abas'], fn($id) => is_numeric($id) && intval($id) > 0);

    if (!empty($abas)) {
        try {
            // Cria placeholders para prepared statements
            $placeholders = implode(',', array_fill(0, count($abas), '?'));

            // Prepara a consulta
            $stmt = $conn->prepare("DELETE FROM abas WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($abas)), ...$abas);

            // Executa a consulta
            if ($stmt->execute()) {
                header('Location: gerenciar_abas.php?msg=success');
            } else {
                // Mensagem de erro em caso de falha na execu«®«ªo
                header('Location: gerenciar_abas.php?msg=error&error=' . urlencode($stmt->error));
            }

            $stmt->close();
        } catch (Exception $e) {
            // Trata erros inesperados
            header('Location: gerenciar_abas.php?msg=exception&error=' . urlencode($e->getMessage()));
        }
    } else {
        // Nenhuma aba v«¡lida foi selecionada
        header('Location: gerenciar_abas.php?msg=none_valid');
    }
} else {
    // Caso a requisi«®«ªo seja inv«¡lida ou n«ªo tenha abas
    header('Location: gerenciar_abas.php?msg=none_selected');
}

$conn->close();
?>
