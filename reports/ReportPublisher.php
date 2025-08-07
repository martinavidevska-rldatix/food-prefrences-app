<?php
namespace reports;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ReportPublisher
{
    /**
     * @throws \Exception
     */
    public function publish(string $reportId): void
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', password: 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('report_queue', false, true, false, false);

        $msg = new AMQPMessage(json_encode(['report_id' => $reportId]));

        $channel->basic_publish($msg, '', 'report_queue');

        $channel->close();
        $connection->close();   
    }
}