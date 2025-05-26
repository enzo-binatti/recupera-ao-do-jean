<?php
require '../service/conexao.php';

function register($nome, $email, $telefone, $senha)
{
    try {
        $conn = new usePDO();
        $instance = $conn->getInstance();
        
        // Verificar se o email já existe
        $sql_check = "SELECT email FROM pessoa WHERE email = ?";
        $stmt_check = $instance->prepare($sql_check);
        $stmt_check->execute([$email]);
        $result_check = $stmt_check->fetch();
        
        if ($result_check) {
            throw new Exception("Este email já está cadastrado!");
        }
        
        // Criptografar senha
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir pessoa
        $sql_pessoa = "INSERT INTO pessoa (nome, telefone, email) VALUES (?, ?, ?)";
        $stmt_pessoa = $instance->prepare($sql_pessoa);
        $stmt_pessoa->execute([$nome, $telefone, $email]);
        
        // Obter ID da pessoa recém-criada
        $id_pessoa = $instance->lastInsertId();
        
        // Inserir usuário
        $sql_usuario = "INSERT INTO usuario (id_pessoa, usuario, senha) VALUES (?, ?, ?)";
        $stmt_usuario = $instance->prepare($sql_usuario);
        $stmt_usuario->execute([$id_pessoa, $nome, $hashed_senha]);
        
        // Gerar sessão
        $session_id = bin2hex(random_bytes(64));
        $sql_session = "INSERT INTO sessions (session_id, user_id) VALUES (?, ?)";
        $stmt_session = $instance->prepare($sql_session);
        $stmt_session->execute([$session_id, $id_pessoa]);
        
        // Registrar o cadastro como primeiro login
        $sql_log = "INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)";
        $stmt_log = $instance->prepare($sql_log);
        $stmt_log->execute([$id_pessoa, $_SERVER['REMOTE_ADDR']]);
        
        // Salvar informações na sessão
        $_SESSION['user_id'] = $id_pessoa;
        $_SESSION['user_name'] = $nome;
        $_SESSION['session_id'] = $session_id;
        
        return true;
    } catch (Exception $e) {
        error_log("Erro no cadastro: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        return false;
    }
}