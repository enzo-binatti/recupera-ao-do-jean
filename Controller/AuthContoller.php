<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/AuthModel.php';

class AuthController {
    private $authModel;

    public function __construct() {
        $this->authModel = new AuthModel();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = trim($_POST['nome']);
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];
            $confirmarSenha = $_POST['confirmarSenha'];

            // Validações
            if (empty($nome) || empty($email) || empty($senha)) {
                return ['success' => false, 'message' => 'Todos os campos são obrigatórios'];
            }

            if ($senha !== $confirmarSenha) {
                return ['success' => false, 'message' => 'As senhas não coincidem'];
            }

            if (strlen($senha) < 8) {
                return ['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres'];
            }

            if ($this->authModel->emailExists($email)) {
                return ['success' => false, 'message' => 'Email já cadastrado'];
            }

            if ($this->authModel->registerUser($nome, $email, $senha)) {
                return ['success' => true, 'message' => 'Cadastro realizado com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Erro ao cadastrar usuário'];
            }
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $senha = $_POST['senha'];

            $user = $this->authModel->getUserByEmail($email);

            if (!$user || !password_verify($senha, $user['senha'])) {
                return ['success' => false, 'message' => 'Email ou senha incorretos'];
            }

            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nome'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];

            return ['success' => true, 'message' => 'Login realizado com sucesso!'];
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logout realizado com sucesso!'];
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);

            if (!$this->authModel->emailExists($email)) {
                return ['success' => false, 'message' => 'Email não cadastrado'];
            }

            $token = bin2hex(random_bytes(32));
            if ($this->authModel->setRecoveryToken($email, $token)) {
                // Simular envio de email
                $link = "http://localhost/sistema_login/reset-password.php?token=$token";
                // mail($email, "Recuperação de Senha", "Clique no link para redefinir sua senha: $link");
                
                return ['success' => true, 'message' => 'Um email com instruções foi enviado'];
            } else {
                return ['success' => false, 'message' => 'Erro ao processar solicitação'];
            }
        }
    }

    public function resetPassword($token) {
        $user = $this->authModel->validateToken($token);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Token inválido ou expirado'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $novaSenha = $_POST['nova_senha'];
            $confirmarSenha = $_POST['confirmar_senha'];

            if ($novaSenha !== $confirmarSenha) {
                return ['success' => false, 'message' => 'As senhas não coincidem'];
            }

            if (strlen($novaSenha) < 8) {
                return ['success' => false, 'message' => 'A senha deve ter pelo menos 8 caracteres'];
            }

            if ($this->authModel->updatePassword($user['email'], $novaSenha)) {
                return ['success' => true, 'message' => 'Senha redefinida com sucesso!'];
            } else {
                return ['success' => false, 'message' => 'Erro ao redefinir senha'];
            }
        }

        return ['success' => true, 'email' => $user['email']];
    }
}