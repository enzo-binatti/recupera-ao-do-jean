<?php
class AuthModel {
    private $conn;

    public function __construct() {
        $db = Database::getInstance();
        $this->conn = $db->getConnection();
    }

    public function registerUser($nome, $email, $senha) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senhaHash);
        return $stmt->execute();
    }

    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function setRecoveryToken($email, $token) {
        $expira = date("Y-m-d H:i:s", strtotime('+1 hour'));
        $stmt = $this->conn->prepare("UPDATE usuarios SET token_recuperacao = ?, token_expira = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expira, $email);
        return $stmt->execute();
    }

    public function validateToken($token) {
        $stmt = $this->conn->prepare("SELECT id, email FROM usuarios WHERE token_recuperacao = ? AND token_expira > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updatePassword($email, $newPassword) {
        $senhaHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE usuarios SET senha = ?, token_recuperacao = NULL, token_expira = NULL WHERE email = ?");
        $stmt->bind_param("ss", $senhaHash, $email);
        return $stmt->execute();
    }
}