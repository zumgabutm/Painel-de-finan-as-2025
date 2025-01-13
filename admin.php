<?php
// Configurações para exibir erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclua o arquivo de conexão
include 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validações simples
    if (empty($username) || empty($password)) {
        $message = 'Por favor, preencha todos os campos.';
    } else {
        // Verifica se o usuário já existe
        $sql = "SELECT id FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            die('Erro na preparação da consulta SELECT: ' . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = 'Nome de usuário já existe.';
        } else {
            // Adiciona o novo usuário
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users (username, password, admin) VALUES (?, ?, 0)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                die('Erro na preparação da consulta INSERT: ' . htmlspecialchars($conn->error));
            }

            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $message = 'Usuário adicionado com sucesso!';
            } else {
                $message = 'Erro ao adicionar usuário: ' . htmlspecialchars($stmt->error);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Adicionar Usuários</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Painel Administrativo</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nome de Usuário" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit">Adicionar Usuário</button>
        </form>
    </div>
</body>
</html>
