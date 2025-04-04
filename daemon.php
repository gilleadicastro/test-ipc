<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando daemon...\n";

// tente acessar a fila
$key = 1234;
$queue = msg_get_queue($key);
if (!$queue) {
    echo "Erro ao obter fila IPC\n";
    exit(1);
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
    echo "Conectado ao banco com sucesso\n";
} catch (PDOException $e) {
    echo "Erro ao conectar no banco: " . $e->getMessage() . "\n";
    exit(1);
}

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
