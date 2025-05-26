<?php
namespace App\Model;

class AuthModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($email, $senha)
    {
        $stmt = $this->conn->prepare("SELECT id, nome, email, senha FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verificar se a senha está correta
            if (password_verify($senha, $user['senha'])) {
                // Gerar um novo ID de sessão
                $session_id = bin2hex(random_bytes(64));
                
                // Salvar a sessão no banco de dados
                $stmt_session = $this->conn->prepare("INSERT INTO sessions (session_id, user_id) VALUES (?, ?)");
                $stmt_session->bind_param("si", $session_id, $user['id']);
                $stmt_session->execute();
                
                // Registrar o login
                $stmt_log = $this->conn->prepare("INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)");
                $stmt_log->bind_param("si", $user['id'], $_SERVER['REMOTE_ADDR']);
                $stmt_log->execute();
                
                // Salvar informações na sessão
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['session_id'] = $session_id;
                
                return true;
            }
        }
        return false;
            $user = $result->fetch_assoc();
            if (password_verify($senha, $user['senha'])) {
                return ['success' => true, 'user' => $user];
            }
        }
        
        return ['success' => false, 'message' => 'Email ou senha inválidos'];
    }

    public function register($nome, $email, $senha)
    {
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Este email já está cadastrado'];
        }

        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("INSERT INTO users (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senha_hash);
        
        if ($stmt->execute()) {
            $user = [
                'id' => $this->conn->insert_id,
                'nome' => $nome,
                'email' => $email
            ];
            return ['success' => true, 'user' => $user];
        }

        return ['success' => false, 'message' => 'Erro ao cadastrar usuário'];
    }

    public function emailExists($email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }

    public function getUser($id)
    {
        $stmt = $this->conn->prepare("SELECT id, nome, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc() ?? null;
    }

    public function forgotPassword($email)
    {
        if (!$this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email não encontrado'];
        }

        // Gere um token de recuperação
        $token = bin2hex(random_bytes(32));
        $token_expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Atualize o token e a data de expiração
        $stmt = $this->conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $token_expiration, $email);
        $stmt->execute();

        // Aqui você pode adicionar o código para enviar o email com o link de recuperação
        // Por exemplo:
        // $reset_url = "https://seusite.com/reset-password?token=$token";
        // enviarEmail($email, 'Recuperação de Senha', "Clique no link para redefinir sua senha: $reset_url");

        return ['success' => true, 'message' => 'Email de recuperação enviado'];
    }

    public function resetPassword($token, $nova_senha)
    {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Token inválido ou expirado'];
        }

        $user_id = $result->fetch_assoc()['id'];
        $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("UPDATE users SET senha = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $stmt->bind_param("si", $nova_senha_hash, $user_id);
        $stmt->execute();

        return ['success' => true, 'message' => 'Senha redefinida com sucesso'];
    }{
}