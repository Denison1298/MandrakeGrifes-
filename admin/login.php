<?php
session_start();

// Incluindo a conexão com o banco de dados
require '../config/conexao.php'; // Certifique-se de que o caminho está correto

// Verifica se o usuário já está logado e redireciona para o painel
if (isset($_SESSION['usuario_logado'])) {
    header("Location: ../admin/painel.php");
    exit();
}

// Processa o formulário de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_input = $_POST['usuario'];
    $senha_input = $_POST['senha'];

    // Prepara a consulta para buscar o usuário no banco de dados
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ?"); 
    $stmt->bind_param("s", $usuario_input); // "s" indica que estamos passando uma string
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o usuário foi encontrado
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();

        // Verificar se a senha fornecida corresponde à senha no banco (sem criptografia)
        if ($senha_input == $usuario['senha']) {
            // A senha está correta, cria a sessão e redireciona para o painel
            $_SESSION['usuario_logado'] = $usuario['username']; // Salva o 'username' na sessão
            header("Location: ../admin/painel.php");
            exit();
        } else {
            $erro = "Usuário ou senha inválidos!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
    <link rel="icon" type="image/png" href="../Css Login/images/icons/favicon.ico"/>
    <link rel="stylesheet" type="text/css" href="../Css Login/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/css/util.css">
    <link rel="stylesheet" type="text/css" href="../Css Login/css/main.css">
</head>
<body>

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">

                <!-- Formulário de login -->
                <form class="login100-form validate-form p-l-55 p-r-55 p-t-178" method="POST" action="login.php">
                    <span class="login100-form-title">
                        Login Admin
                    </span>

                    <?php if (isset($erro)): ?>
                        <p style="color: red;"><?php echo $erro; ?></p>
                    <?php endif; ?>

                    <!-- Campo de usuário -->
                    <div class="wrap-input100 validate-input m-b-16" data-validate="Por favor, insira o nome de usuário">
                        <input class="input100" type="text" name="usuario" placeholder="Usuário" required>
                        <span class="focus-input100"></span>
                    </div>

                    <!-- Campo de senha -->
                    <div class="wrap-input100 validate-input" data-validate="Por favor, insira a senha">
                        <input class="input100" type="password" name="senha" placeholder="Senha" required>
                        <span class="focus-input100"></span>
                    </div>

                    <!-- Esqueci a senha (opcional) -->
                    <div class="text-right p-t-13 p-b-23">
                        <span class="txt1">Esqueceu</span>
                        <a href="#" class="txt2">Usuário / Senha?</a>
                    </div>

                    <!-- Botão de login -->
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" type="submit">Entrar</button>
                    </div>

                    <!-- Link para cadastro de conta -->
                    <div class="flex-col-c p-t-170 p-b-40">
                        <span class="txt1 p-b-9">
                            Não tem uma conta?
                        </span>
                        <a href="#" class="txt3">Cadastre-se agora</a>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Scripts para o funcionamento do estilo -->
    <script src="../Css Login/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="../Css Login/vendor/animsition/js/animsition.min.js"></script>
    <script src="../Css Login/vendor/bootstrap/js/popper.js"></script>
    <script src="../Css Login/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../Css Login/vendor/select2/select2.min.js"></script>
    <script src="../Css Login/vendor/daterangepicker/moment.min.js"></script>
    <script src="../Css Login/vendor/daterangepicker/daterangepicker.js"></script>
    <script src="../Css Login/vendor/countdowntime/countdowntime.js"></script>
    <script src="../Css Login/js/main.js"></script>

</body>
</html>
