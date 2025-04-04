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
?>
<form method="POST">
    <input name="dado" placeholder="Digite algo" />
    <button type="submit">Enviar</button>
</form>
