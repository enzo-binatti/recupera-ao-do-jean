<?php
require_once '../config.php';
require_once '../service/conexao.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validação de dados
    $errors = [];

    if (empty($nome)) {
        $errors[] = "O nome é obrigatório";
    }

    if (empty($email)) {
        $errors[] = "O email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }

    if (empty($telefone)) {
        $errors[] = "O telefone é obrigatório";
    }

    if (empty($senha)) {
        $errors[] = "A senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres";
    }

    if ($senha !== $confirmar_senha) {
        $errors[] = "As senhas não coincidem";
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../view/cadastro, login, recuperar.html?tab=cadastro");
        exit();
    }

    try {
        $conn = new usePDO();
        $instance = $conn->getInstance();

        // Verificar se o email já existe
        $sql_check = "SELECT email FROM usuarios WHERE email = ?";
        $stmt_check = $instance->prepare($sql_check);
        $stmt_check->execute([$email]);
        
        if ($stmt_check->rowCount() > 0) {
            $_SESSION['error'] = "Este email já está cadastrado";
            header("Location: ../view/cadastro, login, recuperar.html?tab=cadastro");
            exit();
        }

        // Criptografar senha
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);

        // Inserir usuário
        $sql = "INSERT INTO usuarios (nome, email, telefone, senha) VALUES (?, ?, ?, ?)";
        $stmt = $instance->prepare($sql);
        $stmt->execute([$nome, $email, $telefone, $hashed_senha]);

        // Gerar sessão
        $session_id = bin2hex(random_bytes(64));
        $user_id = $instance->lastInsertId();
        
        $sql_session = "INSERT INTO sessions (user_id, session_id) VALUES (?, ?)";
        $stmt_session = $instance->prepare($sql_session);
        $stmt_session->execute([$user_id, $session_id]);

        // Registrar login
        $sql_log = "INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)";
        $stmt_log = $instance->prepare($sql_log);
        $stmt_log->execute([$user_id, $_SERVER['REMOTE_ADDR']]);

        // Salvar informações na sessão
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $nome;
        $_SESSION['session_id'] = $session_id;

        header("Location: ../view/bem_vindo.html");
        exit();

    } catch (Exception $e) {
        error_log("Erro no cadastro: " . $e->getMessage());
        $_SESSION['error'] = "Ocorreu um erro ao processar o cadastro. Tente novamente.";
        header("Location: ../view/cadastro, login, recuperar.html?tab=cadastro");
        exit();
    }
} else {
    header("Location: ../view/cadastro, login, recuperar.html?tab=cadastro");
    exit();
}
