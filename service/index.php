<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login & Cadastro</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background: linear-gradient(135deg, #667eea, #764ba2);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      background-color: white;
      width: 100%;
      max-width: 400px;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    h2 {
      text-align: center;
      color: #333;
      margin-bottom: 20px;
    }

    nav {
      display: flex;
      justify-content: space-around;
      margin-bottom: 20px;
    }

    nav button {
      background: none;
      border: none;
      color: #777;
      font-weight: bold;
      cursor: pointer;
      position: relative;
      padding: 10px;
    }

    nav button.active::after {
      content: "";
      position: absolute;
      bottom: -5px;
      left: 0;
      width: 100%;
      height: 2px;
      background-color: #667eea;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #555;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    button.submit-btn {
      width: 100%;
      padding: 10px;
      background-color: #667eea;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button.submit-btn:hover {
      background-color: #5a67d8;
    }

    .hidden {
      display: none;
    }
    
    .alert {
      padding: 10px;
      margin-bottom: 15px;
      border-radius: 6px;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
  </style>
</head>
<body>

  <div class="container">
    <h2>Bem-vindo(a)</h2>
    
    <?php
    // Exibir mensagens de erro ou sucesso
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">Cadastro realizado com sucesso! Faça login para continuar.</div>';
    }
    
    if (isset($_GET['error'])) {
        $errors = explode(",", urldecode($_GET['error']));
        echo '<div class="alert alert-danger">';
        foreach ($errors as $error) {
            echo $error . '<br>';
        }
        echo '</div>';
    }
    
    if (isset($_GET['login_error'])) {
        echo '<div class="alert alert-danger">Email ou senha incorretos.</div>';
    }
    ?>
    
    <nav>
      <button class="tab active" data-tab="login">Login</button>
      <button class="tab" data-tab="cadastro">Cadastro</button>
      <button class="tab" data-tab="recuperar">Esqueceu?</button>
    </nav>

    <!-- Formulário de Login -->
    <form id="login" class="form active" action="processar_login.php" method="post">
      <div class="form-group">
        <label for="email-login">E-mail</label>
        <input type="email" id="email-login" name="email" required />
      </div>
      <div class="form-group">
        <label for="senha-login">Senha</label>
        <input type="password" id="senha-login" name="senha" required />
      </div>
      <button type="submit" class="submit-btn">Entrar</button>
    </form>

    <!-- Formulário de Cadastro -->
    <form id="cadastro" class="form hidden" action="../Controller/cadastroController.php" method="post">
      <div class="form-group">
        <label for="nome-cadastro">Nome</label>
        <input type="text" id="nome-cadastro" name="nome" required />
      </div>
      <div class="form-group">
        <label for="email-cadastro">E-mail</label>
        <input type="email" id="email-cadastro" name="email" required />
      </div>
      <div class="form-group">
        <label for="senha-cadastro">Senha</label>
        <input type="password" id="senha-cadastro" name="senha" required />
      </div>
      <div class="form-group">
        <label for="confirmar-senha">Confirmar Senha</label>
        <input type="password" id="confirmar-senha" name="confirmarSenha" required />
      </div>
      <button type="submit" class="submit-btn">Cadastrar</button>
    </form>

    <!-- Formulário de Recuperação -->
    <form id="recuperar" class="form hidden" action="processar_recuperacao.php" method="post">
      <div class="form-group">
        <label for="email-recuperar">Digite seu E-mail</label>
        <input type="email" id="email-recuperar" name="email" required />
      </div>
      <button type="submit" class="submit-btn">Recuperar Senha</button>
    </form>
  </div>

  <script>
    const tabs = document.querySelectorAll('.tab');
    const forms = document.querySelectorAll('.form');

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const target = tab.dataset.tab;

        // Remove classe ativa dos botões
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        // Esconde todos os formulários
        forms.forEach(form => form.classList.add('hidden'));

        // Mostra o formulário selecionado
        document.getElementById(target).classList.remove('hidden');
      });
    });
    
    // Verificar se há um parâmetro na URL para mostrar uma aba específica
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('tab')) {
      const tab = urlParams.get('tab');
      if (tab === 'cadastro' || tab === 'recuperar') {
        document.querySelector(`.tab[data-tab="${tab}"]`).click();
      }
    }
  </script>
</body>
</html>
