<?php
$key = 1234;
$queue = msg_get_queue($key);
$db = new PDO('sqlite:/app/db/db.sqlite');

echo "Daemon rodando...\n";

while (true) {
    $msgType = null;
    if (msg_receive($queue, 1, $msgType, 1024, $message, true, MSG_IPC_NOWAIT)) {
        echo "Recebido: $message\n";
    
        $stmt = $db->prepare("INSERT INTO dados (conteudo) VALUES (?)");
        $ok = $stmt->execute([$message]);
    
        $resposta = $ok ? "Sucesso ao inserir!" : "Falha na inserção.";
        msg_send($queue, 2, $resposta);
    }
    usleep(500000); // 0.5s
}
