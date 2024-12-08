<?php
session_start();

// Registra o evento de logout no arquivo de log
$log = "Usuário " . $_SESSION['usuario_logado'] . " fez logout em " . date('Y-m-d H:i:s') . "\n";
file_put_contents('../log.txt', $log, FILE_APPEND);  // Ajuste o caminho do log, caso seja necessário

// Destruir a sessão
session_unset();
session_destroy();

// Redireciona para a página de login dentro de 'admin'
header("Location: ../admin/login.php"); // O caminho relativo para 'admin/login.php'
exit();
?>
