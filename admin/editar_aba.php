<?php
include '../config/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitização e validação dos dados recebidos
    $id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : null;
    $nomeAba = isset($_POST['nome_aba']) ? trim($_POST['nome_aba']) : null;

    if ($id && $nomeAba) {
        try {
            // Prepara a consulta SQL
            $stmt = $conn->prepare("UPDATE abas SET nome_aba = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("Erro ao preparar a consulta: " . $conn->error);
            }

            // Vincula os parâmetros
            $stmt->bind_param("si", $nomeAba, $id);

            // Executa a consulta
            if ($stmt->execute()) {
                // Redireciona com sucesso
                header("Location: painel.php?msg=success");
            } else {
                // Redireciona com erro de execução
                header("Location: painel.php?msg=error&error=" . urlencode($stmt->error));
            }

            $stmt->close();
        } catch (Exception $e) {
            // Redireciona em caso de exceção
            header("Location: painel.php?msg=exception&error=" . urlencode($e->getMessage()));
        }
    } else {
        // Redireciona se os dados forem inválidos
        header("Location: painel.php?msg=invalid_data");
    }
    exit();
}
?>
