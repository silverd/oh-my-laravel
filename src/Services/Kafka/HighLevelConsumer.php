<?php

// @see https://github.com/arnaud-lb/php-rdkafka
// @see https://awesomeopensource.com/project/arnaud-lb/php-rdkafka

namespace App\Services\Kafka;

use RdKafka;
use App\Services\AbstractService;

class HighLevelConsumer extends AbstractService
{
    const DEF_CONSUMER_GROUP_ID = 'silverd-consumer-group';

    const DEF_AUTO_OFFSET_RESET = 'earliest';

    public function consume(array $topics, array $params, callable $then, callable $catch)
    {
        $conf = new RdKafka\Conf;

        // Set a rebalance callback to log partition assignments (optional)
        // 当 Topic 的 Partition 数量增加后（扩容），会执行此回调
        $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    $kafka->assign($partitions);
                    break;
                 case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    $kafka->assign(NULL);
                    break;
            }
        });

        // Configure the group.id. All consumer with the same group.id will consume
        // different partitions.
        $conf->set('group.id', $params['group.id'] ?? self::DEF_CONSUMER_GROUP_ID);

        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', $this->config['broker_list']);

        // Set where to start consuming messages when there is no initial offset in
        // offset store or the desired offset is out of range.
        // 'earliest': start from the beginning
        $conf->set('auto.offset.reset', $params['auto.offset.reset'] ?? self::DEF_AUTO_OFFSET_RESET);

        // SASL 加密
        if ($this->config['security_protocol'] ?? '') {
            $conf->set('security.protocol', $this->config['security_protocol']);
            $conf->set('sasl.mechanisms', $this->config['sasl_mechanisms']);
            $conf->set('sasl.username', $this->config['sasl_username']);
            $conf->set('sasl.password', $this->config['sasl_password']);
        }

        $consumer = new RdKafka\KafkaConsumer($conf);

        // 订阅主题
        $consumer->subscribe($topics);

        while (true) {

            // The first argument is the partition (again).
            // The second argument is the timeout.
            $message = $consumer->consume(120 * 1000);

            if ($message === null) {
                sleep(100);
                continue;
            }

            $callback = $message->err == RD_KAFKA_RESP_ERR_NO_ERROR ? $then : $catch;

            // 成功/失败回调
            $callback($message);
        }
    }
}
