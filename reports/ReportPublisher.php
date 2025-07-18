<?php
namespace reports;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ReportPublisher
{
    public function publish(string $reportId): void
    {
        $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', password: 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('report_queue', false, true, false, false);
        $channel->queue_declare('test_queue',  false,  true, false, true);

        

        $msg = new AMQPMessage(json_encode(['report_id' => $reportId]));
        $testMsg = new AMQPMessage(json_encode("the test queue is in progress"));

        $channel->basic_publish($msg, '', 'report_queue');
        $channel->basic_publish($testMsg,'','test_queue');

        $channel->close();
        $connection->close();   
    }
}