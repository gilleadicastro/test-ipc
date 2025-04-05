<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dado = $_POST['dado'] ?? '';
    $key = 1234;
    $queue = msg_get_queue($key);

    $enviado = msg_send($queue, 1, $dado);
    if (!$enviado) {
        echo "<p>Erro ao enviar a mensagem para o daemon.</p>";
        exit;
    }

    $recebido = msg_receive($queue, 2, $msgType, 1024, $resposta, true, MSG_IPC_NOWAIT);
    if ($recebido) {
        echo "<p>Resposta do servidor: $resposta</p>";
    } else {
        echo "<p>Nenhuma resposta recebida.</p>";
    }
}

$db = new PDO(
    sprintf('pgsql:host=%s;port=%s;dbname=%s', getenv('PGHOST'), getenv('PGPORT'), getenv('PGDATABASE')),
    getenv('PGUSER'),
    getenv('PGPASSWORD')
);

$stmt = $db->prepare("SELECT * FROM dados");
$stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($dados)

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IPS</title>
</head>
<body>
    <form method="POST">
        <input name="dado" placeholder="Digite algo" />
        <button type="submit">Enviar</button>
    </form>
    <table border="1">
        <thead>
            <tr><th>Dado</th></tr>
        </thead>
        <tbody>
            <?php foreach ($dados as $linha): ?>
                <tr>
                    <td><?= htmlspecialchars($linha['coluna']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>