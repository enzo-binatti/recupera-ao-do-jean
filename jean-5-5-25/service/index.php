<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>acesso</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 350px;
            text-align: center;
        }
        
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        
        button:hover {
            background-color: #45a049;
        }
        
        .link {
            color: #4CAF50;
            cursor: pointer;
            margin-top: 15px;
            display: block;
        }
        
        .link:hover {
            text-decoration: underline;
        }
        
        .hidden {
            display: none;
        }
        
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Formulário de Login -->
        <div id="login-form">
            <h2>Login</h2>
            <div id="login-error" class="error hidden"></div>
            <input type="email" id="login-email" placeholder="E-mail" required>
            <input type="password" id="login-password" placeholder="Senha" required>
            <button onclick="login()">Entrar</button>
            <span class="link" onclick="showForm('register-form')">Não tem uma conta? Cadastre-se</span>
            <span class="link" onclick="showForm('recover-form')">Esqueci minha senha</span>
        </div>
        
        <!-- Formulário de Cadastro -->
        <div id="register-form" class="hidden">
            <h2>Cadastro</h2>
            <div id="register-error" class="error hidden"></div>
            <input type="text" id="register-name" placeholder="Nome completo" required>
            <input type="email" id="register-email" placeholder="E-mail" required>
            <input type="password" id="register-password" placeholder="Senha" required>
            <input type="password" id="register-confirm-password" placeholder="Confirme a senha" required>
            <input type="number" id="register-telefone" placeholder="telefone" required>
            <button onclick="register()">Cadastrar</button>
            <span class="link" onclick="showForm('login-form')">Voltar para o login</span>
        </div>
        
        <!-- Formulário de Recuperação de Senha -->
        <div id="recover-form" class="hidden">
            <h2>Recuperar Senha</h2>
            <div id="recover-error" class="error hidden"></div>
            <div id="recover-success" class="hidden">Um e-mail com instruções foi enviado para o endereço fornecido.</div>
            <input type="email" id="recover-email" placeholder="Digite seu e-mail" required>
            <button onclick="recoverPassword()">Recuperar Senha</button>
            <span class="link" onclick="showForm('login-form')">Voltar para o login</span>
        </div>

        <div id="recover-form" class="hidden">
            <h2>telefone</h2>
            <div id="recover-error" class="error hidden"></div>
            <div id="recover-success" class="hidden">telefone</div>
            <input type="number" id="telefone" placeholder="Digite seu telefone" required>
        </div>
    </div>

    <script>        // Simulação de "banco de dados" em localStorage
        if (!localStorage.getItem('users')) {
            localStorage.setItem('users', JSON.stringify([]));
        }
        
        function showForm(formId) {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('recover-form').classList.add('hidden');
            
            document.getElementById(formId).classList.remove('hidden');
            
            // Limpar erros
            document.getElementById('login-error').classList.add('hidden');
            document.getElementById('register-error').classList.add('hidden');
            document.getElementById('recover-error').classList.add('hidden');
            document.getElementById('recover-success').classList.add('hidden');
        }
        
        function login() {
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const errorElement = document.getElementById('login-error');
            
            const users = JSON.parse(localStorage.getItem('users'));
            const user = users.find(u => u.email === email && u.password === password);
            
            if (user) {
                // Redireciona para bem_vindo.html após login bem-sucedido
                window.location.href = 'bem_vindo.html';
            } else {
                errorElement.textContent = "E-mail ou senha incorretos.";
                errorElement.classList.remove('hidden');
            }
        }
        
        function register() {
            const name = document.getElementById('register-name').value;
            const email = document.getElementById('register-email').value;
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('register-confirm-password').value;
            const errorElement = document.getElementById('register-error');
            
            // Validações
            if (password !== confirmPassword) {
                errorElement.textContent = "As senhas não coincidem.";
                errorElement.classList.remove('hidden');
                return;
            }
            
            if (password.length < 6) {
                errorElement.textContent = "A senha deve ter pelo menos 6 caracteres.";
                errorElement.classList.remove('hidden');
                return;
            }
            
            const users = JSON.parse(localStorage.getItem('users'));
            
            // Verifica se o e-mail já está cadastrado
            if (users.some(u => u.email === email)) {
                errorElement.textContent = "Este e-mail já está cadastrado.";
                errorElement.classList.remove('hidden');
                return;
            }
            
            // Adiciona o novo usuário
            users.push({ name, email, password });
            localStorage.setItem('users', JSON.stringify(users));
            
            alert("Cadastro realizado com sucesso! Faça login para continuar.");
            showForm('login-form');
        }
        
        function recoverPassword() {
            const email = document.getElementById('recover-email').value;
            const errorElement = document.getElementById('recover-error');
            const successElement = document.getElementById('recover-success');
            
            const users = JSON.parse(localStorage.getItem('users'));
            const userExists = users.some(u => u.email === email);
            
            if (userExists) {
                // Em um sistema real, você enviaria um e-mail com um link para redefinir a senha
                successElement.classList.remove('hidden');
                errorElement.classList.add('hidden');
            } else {
                errorElement.textContent = "E-mail não encontrado.";
                errorElement.classList.remove('hidden');
                successElement.classList.add('hidden');
            }
        }</script>
</body>
</html>
