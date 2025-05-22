<?php
require_once '../model/cadastroModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados do formulário
    $fullname = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['senha'] ?? '';
    $confirmPassword = $_POST['confirmarSenha'] ?? '';
    
    // Validar dados
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "O nome é obrigatório";
    }
    
    if (empty($email)) {
        $errors[] = "O email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    if (empty($password)) {
        $errors[] = "A senha é obrigatória";
    } elseif (strlen($password) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "As senhas não coincidem";
    }
    
    // Se não houver erros, registrar o usuário
    if (empty($errors)) {
        $result = register($fullname, $email, $password);
        
        if ($result > 0) {
            // Redirecionar para a página de login com mensagem de sucesso
            header("Location: ../view/login.php?success=1");
            exit();
        } else {
            $errors[] = "Não foi possível realizar o cadastro. Tente novamente.";
        }
    }
    
    // Se houver erros, redirecionar de volta para o formulário com os erros
    if (!empty($errors)) {
        $error_string = implode(",", $errors);
        header("Location: ../view/cadastro.php?error=" . urlencode($error_string));
        exit();
    }
} else {
    // Se não for uma requisição POST, redirecionar para o formulário
    header("Location: ../view/cadastro.php");
    exit();
}