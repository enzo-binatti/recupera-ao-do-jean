<?php
require_once '../config.php';
require_once '../service/conexao.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $_SESSION['error'] = "Por favor, preencha todos os campos.";
        header("Location: ../view/cadastro, login, recuperar.html?tab=login");
        exit();
    }

    try {
        $conn = new usePDO();
        $instance = $conn->getInstance();

        // Buscar usuário
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $instance->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Gerar sessão
            $session_id = bin2hex(random_bytes(64));
            $sql_session = "INSERT INTO sessions (user_id, session_id) VALUES (?, ?)";
            $stmt_session = $instance->prepare($sql_session);
            $stmt_session->execute([$usuario['id'], $session_id]);

            // Registrar login
            $sql_log = "INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)";
            $stmt_log = $instance->prepare($sql_log);
            $stmt_log->execute([$usuario['id'], $_SERVER['REMOTE_ADDR']]);

            // Salvar informações na sessão
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['session_id'] = $session_id;

            header("Location: ../view/bem_vindo.html");
            exit();
        } else {
            $_SESSION['error'] = "Email ou senha incorretos.";
            header("Location: ../view/cadastro, login, recuperar.html?tab=login");
            exit();
        }
    } catch (Exception $e) {
        error_log("Erro no login: " . $e->getMessage());
        $_SESSION['error'] = "Ocorreu um erro ao processar o login. Tente novamente.";
        header("Location: ../view/cadastro, login, recuperar.html?tab=login");
        exit();
    }
} else {
    header("Location: ../view/cadastro, login, recuperar.html?tab=login");
    exit();
}
