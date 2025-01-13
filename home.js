$(document).ready(function() {
    // Função para desenhar o gráfico
    function drawChart(data) {
        const ctx = document.getElementById('financeChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Ganhos', 'Gastos'],
                datasets: [{
                    label: 'Total',
                    data: [data.ganhos, data.gastos],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Função para atualizar a tabela com os dados do servidor
    function updateTable() {
        $.get('home.php', function(data) {
            $('#transactionTable').html($(data).find('#transactionTable').html());
        });
    }

    // Inicializa o gráfico com os dados existentes
    const chartData = JSON.parse($('#chartData').text());
    drawChart(chartData);

    $('#transactionForm').submit(function(event) {
        event.preventDefault();

        $.ajax({
            url: 'home.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert(response); // Mostra a mensagem de sucesso ou erro
                // Limpar o formulário
                $('#transactionForm')[0].reset();
                // Atualizar a tabela e o gráfico
                updateTable();
            }
        });
    });

    $('#transactionTable').on('click', '.delete-btn', function() {
        const row = $(this).closest('tr');
        const transactionId = row.data('id');

        $.ajax({
            url: 'home.php',
            type: 'POST',
            data: { action: 'delete', transactionId: transactionId },
            success: function(response) {
                alert(response); // Mostra a mensagem de sucesso ou erro
                // Remover a linha da tabela
                row.remove();
                // Atualizar a tabela e o gráfico
                updateTable();
            }
        });
    });

    $('#clearAllBtn').click(function() {
        if (confirm('Tem certeza de que deseja limpar todos os registros?')) {
            $.ajax({
                url: 'home.php',
                type: 'POST',
                data: { action: 'clearAll' },
                success: function(response) {
                    alert(response); // Mostra a mensagem de sucesso ou erro
                    // Atualizar a tabela e o gráfico
                    updateTable();
                }
            });
        }
    });

    $('#logoutBtn').click(function() {
        $.ajax({
            url: 'home.php',
            type: 'POST',
            data: { action: 'logout' },
            success: function(response) {
                window.location.href = 'index.php';
            }
        });
    });
});
