<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dado = $_POST['dado'] ?? '';
    $key = 1234;
    $queue = msg_get_queue($key);

    $enviado = msg_send($queue, 1, $dado);
    if (!$enviado) {
        echo "<p class='error'>Erro ao enviar a mensagem para o daemon.</p>";
        exit;
    }

    $recebido = msg_receive($queue, 2, $msgType, 1024, $resposta, true, MSG_IPC_NOWAIT);
    if ($recebido) {
        echo "<p class='success'>Resposta do servidor: " . htmlspecialchars($resposta) . "</p>";
    } else {
        echo "<p class='warning'>Nenhuma resposta recebida.</p>";
    }
}

try {
    $db = new PDO(
        sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            getenv('PGHOST'),
            getenv('PGPORT'),
            getenv('PGDATABASE')
        ),
        getenv('PGUSER'),
        getenv('PGPASSWORD')
    );
    error_log("Conectado ao banco com sucesso", 4);
} catch (PDOException $e) {
    error_log("Erro ao conectar no banco: " . $e->getMessage(), 4);
    exit(1);
}

    $stmt = $db->prepare("SELECT * FROM dados");
    $stmt->execute();
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>IPS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        body {
            background: #f7f9fc;
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #2c3e50;
        }

        form {
            max-width: 500px;
            margin: 0 auto 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        input[name="dado"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            padding: 12px 20px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #27ae60;
        }

        .success, .warning, .error {
            max-width: 500px;
            margin: 10px auto;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            font-weight: bold;
        }

        .success {
            background-color: #eafaf1;
            color: #2ecc71;
        }

        .warning {
            background-color: #fff8e1;
            color: #f39c12;
        }

        .error {
            background-color: #fdecea;
            color: #e74c3c;
        }

        table {
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        thead {
            background-color: #3498db;
            color: white;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        tbody tr:hover {
            background-color: #f0f8ff;
        }
    </style>
</head>
<body>
    <h1>Envio de Mensagens - IPC + PostgreSQL</h1>

    <form method="POST">
        <input name="dado" placeholder="Digite algo" required />
        <button type="submit">Enviar</button>
    </form>

    <table>
        <thead>
            <tr><th>Conteúdo</th></tr>
        </thead>
        <tbody>
        <?php foreach ($dados as $linha): ?>
            <tr>
                <td><?= htmlspecialchars($linha['conteudo']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
