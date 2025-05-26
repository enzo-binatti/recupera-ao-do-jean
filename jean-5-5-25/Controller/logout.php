<?php
require_once '../config.php';
require_once '../service/conexao.php';

session_start();

// Se o usuário está logado
if (isset($_SESSION['user_id']) && isset($_SESSION['session_id'])) {
    try {
        $conn = new usePDO();
        $instance = $conn->getInstance();

        // Remover sessão do banco
        $sql = "DELETE FROM sessions WHERE session_id = ?";
        $stmt = $instance->prepare($sql);
        $stmt->execute([$_SESSION['session_id']]);

        // Registrar logout
        $sql_log = "INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)";
        $stmt_log = $instance->prepare($sql_log);
        $stmt_log->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);

        // Destruir a sessão
        session_destroy();
    } catch (Exception $e) {
        error_log("Erro no logout: " . $e->getMessage());
    }
}

// Redirecionar para a página de login
header("Location: ../view/cadastro, login, recuperar.html");
exit();
