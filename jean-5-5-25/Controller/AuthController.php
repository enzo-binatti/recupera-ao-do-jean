<?php
require_once __DIR__ . '/../service/conexao.php';

class AuthController {
    private $conexao;
    
    public function __construct() {
        $this->conexao = conexao();
    }
    
    public function cadastrar($nome, $email, $senha) {
        try {
            // Verificar se o email já existe
            $sql_check = "SELECT id FROM users WHERE email = ?";
            $stmt_check = $this->conexao->prepare($sql_check);
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) {
                return ['success' => false, 'message' => 'Este email já está cadastrado'];
            }
            
            // Criptografar senha
            $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
            
            // Inserir usuário
            $sql_insert = "INSERT INTO users (nome, email, senha) VALUES (?, ?, ?)";
            $stmt_insert = $this->conexao->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $nome, $email, $hashed_senha);
            
            if ($stmt_insert->execute()) {
                // Obter ID do usuário recém-criado
                $user_id = $this->conexao->insert_id;
                
                // Gerar sessão para o novo usuário
                $session_id = bin2hex(random_bytes(64));
                
                // Salvar sessão
                $sql_session = "INSERT INTO sessions (session_id, user_id) VALUES (?, ?)";
                $stmt_session = $this->conexao->prepare($sql_session);
                $stmt_session->bind_param("si", $session_id, $user_id);
                
                if (!$stmt_session->execute()) {
                    throw new Exception("Erro ao salvar sessão: " . $stmt_session->error);
                }
                
                // Registrar o cadastro como primeiro login
                $sql_log = "INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)";
                $stmt_log = $this->conexao->prepare($sql_log);
                $stmt_log->bind_param("si", $user_id, $_SERVER['REMOTE_ADDR']);
                
                if (!$stmt_log->execute()) {
                    throw new Exception("Erro ao registrar login: " . $stmt_log->error);
                }
                
                // Salvar informações na sessão
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $nome;
                $_SESSION['session_id'] = $session_id;
                
                return ['success' => true, 'message' => 'Cadastro realizado com sucesso'];
            } else {
                throw new Exception("Erro ao inserir usuário: " . $stmt_insert->error);
            }
            
        } catch (Exception $e) {
            error_log("Erro no cadastro: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }
    
    public function login($email, $senha) {
        try {
            $sql = "SELECT id, nome, email, senha FROM users WHERE email = ?";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($senha, $user['senha'])) {
                    // Gerar novo ID de sessão
                    $session_id = bin2hex(random_bytes(64));
                    
                    // Salvar sessão no banco de dados
                    $sql_session = "INSERT INTO sessions (session_id, user_id) VALUES (?, ?)";
                    $stmt_session = $this->conexao->prepare($sql_session);
                    $stmt_session->bind_param("si", $session_id, $user['id']);
                    
                    if (!$stmt_session->execute()) {
                        throw new Exception("Erro ao salvar sessão: " . $stmt_session->error);
                    }
                    
                    // Registrar o login
                    $sql_log = "INSERT INTO login_logs (user_id, ip_address) VALUES (?, ?)";
                    $stmt_log = $this->conexao->prepare($sql_log);
                    $stmt_log->bind_param("si", $user['id'], $_SERVER['REMOTE_ADDR']);
                    
                    if (!$stmt_log->execute()) {
                        throw new Exception("Erro ao registrar login: " . $stmt_log->error);
                    }
                    
                    // Salvar informações na sessão
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nome'];
                    $_SESSION['session_id'] = $session_id;
                    
                    return ['success' => true, 'message' => 'Login realizado com sucesso'];
                } else {
                    return ['success' => false, 'message' => 'Senha incorreta'];
                }
            } else {
                return ['success' => false, 'message' => 'Email não encontrado'];
            }
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()];
        }
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function obterUsuario() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $sql = "SELECT id, user FROM users WHERE id = ?";
            $stmt = $this->conexao->prepare($sql);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
}