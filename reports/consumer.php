<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use reports\ReportGenerator;

try {
    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', password: 'guest');
    $channel = $connection->channel();
    $channel->queue_declare('report_queue', false, true, false, false);

    echo "[*] Waiting for messages. To exit press CTRL+C\n";
    $channel->basic_consume('report_queue', '', false, false, false, false, function (AMQPMessage $msg) {
        echo "[x] Message received: " . $msg->getBody() . "\n";


        try {
            $data = json_decode($msg->getBody(), true);
            $reportId = $data['report_id'] ?? null;

            if (!$reportId) {
                throw new Exception("Missing report_id");
            }

            echo "[x] Generating report for: $reportId\n";

            $pdo = new PDO('mysql:host=db;dbname=mydatabase', 'user', 'secret', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $reportDir = __DIR__ . '/../reports';
            $generator = new ReportGenerator($pdo);
            $path = $generator->generateReport($reportId);

            echo "[✓] Report saved: $path\n";
            $msg->ack();

        } catch (Exception $e) {
            echo "[!] Error: " . $e->getMessage() . "\n";
            $msg->nack(false, false); // Reject without requeue
        }
    });

    while ($channel->is_consuming()) {
        $channel->wait();
    }

    $channel->close();
    $connection->close();
} catch (Exception $e) {
    echo "[FATAL] Could not start consumer: " . $e->getMessage() . "\n";
    exit(1);
}
