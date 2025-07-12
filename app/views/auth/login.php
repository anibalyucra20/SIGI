<?php require '../layouts/header.php'; ?>
<style>
    body {
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        font-family: 'Arial', sans-serif;
        background: linear-gradient(135deg, #6e45e2, #88d3ce);
        color: #fff;
    }

    .login-container {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        text-align: center;
        width: 500px;
    }

    .login-container h1 {
        font-size: 2rem;
        margin-bottom: 20px;
        color: #fff;
    }

    .login-container input {
        width: 100%;
        padding: 10px;
        margin: 10px 0;
        border: none;
        border-radius: 5px;
        outline: none;
        font-size: 1rem;
    }

    .login-container input[type="email"],
    .login-container input[type="password"] {
        background: rgba(255, 255, 255, 0.8);
        color: #333;
    }

    .login-container input::placeholder {
        color: #888;
    }

    .login-container button {
        width: 100%;
        padding: 10px;
        margin-top: 20px;
        background: #6e45e2;
        border: none;
        border-radius: 5px;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .login-container button:hover {
        background: #88d3ce;
    }

    .login-container a {
        display: block;
        margin-top: 15px;
        color: #fff;
        text-decoration: none;
        font-size: 0.9rem;
    }

    .login-container a:hover {
        text-decoration: underline;
    }
</style>
<div class="login-container">
    <h1>Iniciar Sesión</h1>
    <img src="https://sispa.iestphuanta.edu.pe/img/logo.png" alt="" width="100%">
    <h3>SIGI</h3>
    <h5>Sistema Integrado de Gestión Institucional</h6>
    <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Credenciales incorrectas</div>
        <?php endif; ?>
    <form action="<?= BASE_URL ?>/login/acceder" method="post">
      <input type="text" name="dni" id="dni" placeholder="DNI" required>
      <input type="password" name="password" id="password" placeholder="Contraseña" required>
      <button type="submit">Ingresar</button>
    </form>
    <a href="#">¿Olvidaste tu contraseña?</a>
  </div>
<?php require '../layouts/footer.php'; ?>