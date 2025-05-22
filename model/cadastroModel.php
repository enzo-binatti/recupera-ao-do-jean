<?php
require_once '../service/conexao.php';

function register($fullname, $email, $password)
{
    // Obter a conexão com o banco de dados
    $conexao = getConexao();
    
    // Criptografar senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // SQL para inserir usuário
    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    
    // Preparar e executar a consulta
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);
    $stmt->execute();
    
    // Verificar se o registro foi bem-sucedido
    $result = $stmt->affected_rows;
    
    return $result;
}