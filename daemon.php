<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("Iniciando daemon...", 4);

if (!function_exists('msg_get_queue')) {
    error_log("Função msg_get_queue não existe. IPC não está disponível.", 4);
    exit(1);
}

$key = 1234;
$queue = msg_get_queue($key);
if (!$queue) {
    error_log("Erro ao obter fila IPC", 4);
    exit(1);
}

// Mostra variáveis de ambiente do PostgreSQL
error_log("PGHOST=" . getenv('PGHOST'), 4);
error_log("PGPORT=" . getenv('PGPORT'), 4);
error_log("PGDATABASE=" . getenv('PGDATABASE'), 4);
error_log("PGUSER=" . getenv('PGUSER'), 4);
error_log("PGPASSWORD=" . getenv('PGPASSWORD'), 4);

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
