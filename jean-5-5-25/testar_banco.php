<?php
require_once 'config.php';
require_once 'service/conexao.php';

try {
    $conn = new usePDO();
    $instance = $conn->getInstance();
    
    // Testar conexão
    if ($instance) {
        echo "Conexão com o banco de dados estabelecida com sucesso!\n";
    } else {
        throw new Exception("Não foi possível conectar ao banco de dados");
    }
    
    // Testar inserção
    $nome = "Teste " . time();
    $email = "teste" . time() . "@teste.com";
    $telefone = "999999999";
    $senha = password_hash("123456", PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO usuarios (nome, email, telefone, senha) VALUES (?, ?, ?, ?)";
    $stmt = $instance->prepare($sql);
    $result = $stmt->execute([$nome, $email, $telefone, $senha]);
    
    if ($result) {
        echo "Usuário inserido com sucesso!\n";
        echo "ID do usuário: " . $instance->lastInsertId() . "\n";
    } else {
        throw new Exception("Erro ao inserir usuário");
    }
    
    // Verificar se o usuário foi inserido
    $sql_check = "SELECT * FROM usuarios WHERE email = ?";
    $stmt_check = $instance->prepare($sql_check);
    $stmt_check->execute([$email]);
    $usuario = $stmt_check->fetch();
    
    if ($usuario) {
        echo "Usuário encontrado no banco de dados:\n";
        echo "ID: " . $usuario['id'] . "\n";
        echo "Nome: " . $usuario['nome'] . "\n";
        echo "Email: " . $usuario['email'] . "\n";
        echo "Telefone: " . $usuario['telefone'] . "\n";
    } else {
        throw new Exception("Usuário não encontrado no banco de dados");
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    error_log("Erro no teste do banco: " . $e->getMessage());
}
