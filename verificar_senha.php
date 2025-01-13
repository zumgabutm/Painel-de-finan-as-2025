<?php
// Inclua o arquivo de configuração do banco de dados
include('db.php');

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    // Obtém os dados do formulário
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Consulta o banco de dados para obter o hash da senha do usuário
    $sql = "SELECT password FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Obtém o hash da senha do banco de dados
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        // Verifica se a senha fornecida corresponde ao hash armazenado
        if (password_verify($password, $hashed_password)) {
            echo "Senha correta!";
        } else {
            echo "Senha incorreta!";
        }
    } else {
        echo "Usuário não encontrado!";
    }
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
