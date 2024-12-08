<?php
$servername = "localhost";
$username = "mandr615_Denison";
$password = "0102@@Deh";
$dbname = "mandr615_MandrakeGrifes"; // Nome do banco de dados

// Criação da conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificação de erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
?>
