<?php
 
require '../model/CadastroModel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obter dados do formulário
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmarSenha'] ?? '';
    
    // Validar dados
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome é obrigatório";
    }
    
    if (empty($email)) {
        $errors[] = "O email é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }
    
    if (empty($senha)) {
        $errors[] = "A senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $errors[] = "A senha deve ter pelo menos 6 caracteres";
    }
    
    if ($senha !== $confirmarSenha) {
        $errors[] = "As senhas não coincidem";
    }
    
    // Se não houver erros, registrar o usuário
    if (empty($errors)) {
        $resultado = register($nome, $email, $telefone, $senha);
        
        if ($resultado) {
            // Redirecionar para bem_vindo.php
            header("Location: ../view/bem_vindo.php");
            exit();
        } else {
            $errors[] = "Não foi possível realizar o cadastro. Tente novamente.";
        }
    }
    
    // Se houver erros, redirecionar de volta para o formulário com os erros
    $_SESSION['errors'] = $errors;
    header("Location: ../view/cadastro.php");
    exit();
} else {
    header("Location: ../view/cadastro.php");
    exit();
}
}