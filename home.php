<?php
session_start();
include('db.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit();
}

// Verificar se a solicitação é POST e se contém dados para inserção, exclusão, ou limpeza
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['description']) && isset($_POST['amount']) && isset($_POST['type'])) {
        $description = $conn->real_escape_string($_POST['description']);
        $amount = $conn->real_escape_string($_POST['amount']);
        $type = $conn->real_escape_string($_POST['type']);
        $date = date('Y-m-d');
        $time = date('H:i:s');

        $username = $_SESSION['username'];
        $userQuery = "SELECT id FROM users WHERE username = '$username'";
        $userResult = $conn->query($userQuery);

        if ($userResult && $userResult->num_rows > 0) {
            $userRow = $userResult->fetch_assoc();
            $user_id = $userRow['id'];

            // Inserir a transação
            $sql = "INSERT INTO transactions (description, amount, type, date, time, user_id) VALUES ('$description', '$amount', '$type', '$date', '$time', '$user_id')";
            if ($conn->query($sql)) {
                echo "Transação adicionada com sucesso!";
            } else {
                echo "Erro ao adicionar transação: " . $conn->error;
            }
        }
        exit(); // Termina o script após a inserção para não continuar processando o restante do código
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $transactionId = intval($_POST['transactionId']);
        $deleteQuery = "DELETE FROM transactions WHERE id = $transactionId";
        if ($conn->query($deleteQuery)) {
            echo "Transação removida com sucesso!";
        } else {
            echo "Erro ao remover transação: " . $conn->error;
        }
        exit(); // Termina o script após a exclusão para não continuar processando o restante do código
    } elseif (isset($_POST['action']) && $_POST['action'] === 'clearAll') {
        $username = $_SESSION['username'];
        $userQuery = "SELECT id FROM users WHERE username = '$username'";
        $userResult = $conn->query($userQuery);

        if ($userResult && $userResult->num_rows > 0) {
            $userRow = $userResult->fetch_assoc();
            $user_id = $userRow['id'];

            $clearQuery = "DELETE FROM transactions WHERE user_id = $user_id";
            if ($conn->query($clearQuery)) {
                echo "Todos os registros foram limpos com sucesso!";
            } else {
                echo "Erro ao limpar todos os registros: " . $conn->error;
            }
        }
        exit(); // Termina o script após a exclusão para não continuar processando o restante do código
    } elseif (isset($_POST['action']) && $_POST['action'] === 'logout') {
        session_destroy();
        header('Location: index.php');
        exit();
    }
}

// Consulta das transações do usuário logado
$username = $_SESSION['username'];
$userQuery = "SELECT id FROM users WHERE username = '$username'";
$userResult = $conn->query($userQuery);

if ($userResult && $userResult->num_rows > 0) {
    $userRow = $userResult->fetch_assoc();
    $user_id = $userRow['id'];

    $transactionsQuery = "SELECT * FROM transactions WHERE user_id = '$user_id' ORDER BY date DESC, time DESC";
    $transactionsResult = $conn->query($transactionsQuery);
} else {
    die("Erro ao obter ID do usuário.");
}

if (!$transactionsResult) {
    die("Erro na consulta: " . $conn->error);
}

// Calcular total de ganhos e gastos
$totalGanhos = 0;
$totalGastos = 0;
$transactionRows = [];

while ($row = $transactionsResult->fetch_assoc()) {
    $transactionRows[] = $row;
    if ($row['type'] === 'ganho') {
        $totalGanhos += $row['amount'];
    } elseif ($row['type'] === 'gasto') {
        $totalGastos += $row['amount'];
    }
}

// Retornar dados do gráfico em formato JSON
$chartData = json_encode([
    'ganhos' => $totalGanhos,
    'gastos' => $totalGastos
]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sistema Financeiro</title>
    <link rel="stylesheet" href="home.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="home-container">
<h1><img src="https://miro.medium.com/v2/resize:fit:800/1*7B3LcW3rYEETBmaDdLxZDQ.gif" alt="Gráfico" style="width: 25px; height: 25px;"> 🛠️GERENCIAMENTO🛠️<img src="https://miro.medium.com/v2/resize:fit:800/1*7B3LcW3rYEETBmaDdLxZDQ.gif"alt="Carteira" style="width: 02px; height: 2px;"></h1>
        <form id="transactionForm">
            <input type="text" name="description" placeholder="Descrição" required>
            <input type="number" name="amount" placeholder="Valor" required>
            <select name="type" required>
                <option value="ganho">💰LUCRO💰 ⬅️OPÇAO⬅️</option>
                <option value="gasto">💸GASTOS💸 ⬅️OPÇAO⬅️</option>
            </select>
            <button type="submit">➕ADICIONAR➕</button>
            <br>
        </form>
        <button id="clearAllBtn">❌LIMPAR TODOS OS 🗑️REGISTROS🗑️</button>
        <br>
        <button id="logoutBtn">🔚SAIR🔚</button>
        <div class="totals">
            <h2>💵TOTAL💵</h2>
            <p>💰LUCROS💰 R$ <?= number_format($totalGanhos, 2, ',', '.') ?></p>
            <p>💸GASTOS💸 R$ <?= number_format($totalGastos, 2, ',', '.') ?></p>
        </div>
        <div class="transactions">
            <h2>🔄MOVIMENTAÇOES🔄</h2>
            <table>
                <thead>
                    <tr>
                        <th>📋Descrição</th>
                        <th>💵Valor</th>
                        <th>🏷️Tipo</th>
                        <th>📅Data</th>
                        <th>⏰Hora</th>
                        <th>⚙️Ação</th>
                    </tr>
                </thead>
                <tbody id="transactionTable">
                    <?php foreach ($transactionRows as $row): ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td>R$ <?= number_format($row['amount'], 2, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td><?= htmlspecialchars($row['time']) ?></td>
                            <td><button class="delete-btn">🗑️</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <canvas id="financeChart"></canvas>
        <script id="chartData" type="application/json"><?= $chartData ?></script>
    </div>
    <script src="home.js"></script>
</body>
</html>
