<?php
$host = "localhost";
$usuario = "root";
$senha = "";
$banco = "seu_banco";

// Cria a conexão
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Verifica se houve erro
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// Se quiser exibir sucesso:
// echo "Conexão realizada com sucesso!";
?>
