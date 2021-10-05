<?php

namespace Silverd\OhMyLaravel\Services\Kafka;

use RdKafka;
use Silverd\OhMyLaravel\Services\AbstractService;

class Producer extends AbstractService
{
    const MAX_FLUSH_RETRIES = 10;

    public function produce(string $topic, string $payload, ?callable $errorCb = null, ?callable $drMsgCb = null)
    {
        return $this->produces($topic, [$payload], $errorCb, $drMsgCb);
    }

    public function produces(string $topic, array $payloads, ?callable $errorCb = null, ?callable $drMsgCb = null)
    {
        $conf = new RdKafka\Conf;

        // Initial list of Kafka brokers
        $conf->set('metadata.broker.list', $this->config['broker_list']);

        // If you need to produce exactly once and want to keep the original produce order, uncomment the line below
        // @see http://www.jasongj.com/kafka/transaction
        $conf->set('enable.idempotence', 'true');

        if ($errorCb) {
            // The error callback is used by librdkafka to signal ciritcal errors back to the application
            $conf->setErrorCb($errorCb);
        }

        if ($drMsgCb) {
            // The callback is called when a message is succesfully produced
            // or if librdkafka encountered a permanent failure,
            // or the retry counter for temporary errors has been exhausted.
            // @see https://arnaud.le-blanc.net/php-rdkafka-doc/phpdoc/rdkafka-conf.setdrmsgcb.html
            $conf->setDrMsgCb($drMsgCb);
        }

        $producer = new RdKafka\Producer($conf);

        // ACK 机制
        $cf = new RdKafka\TopicConf;
        $cf->set('request.required.acks', -1);

        $topic = $producer->newTopic($topic, $cf);

        // 生产消息
        foreach ($payloads as $payload) {
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, $payload);
        }

        // 生产消息是异步的，将消息放入到内部队列后会立刻返回
        // 因此需要由 poll 返回最终写入结果
        $producer->poll(0);

        for ($i = 0; $i < self::MAX_FLUSH_RETRIES; $i++) {
            // Wait until all outstanding produce requests, et.al, are completed.
            // This should typically be done prior to destroying a producer instance to
            // make sure all queued and in-flight produce requests are completed before terminating.
            // This function will call poll() and thus trigger callbacks.
            $result = $producer->flush(10000);
            if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                break;
            }
        }

        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!');
        }
    }
}
