<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Handler;

use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\BufferHandler;
use Monolog\Formatter\FormatterInterface;
use Illuminate\Cache\Repository as CacheRepository;

class DeduplicationHandler extends BufferHandler
{
    protected $cache;
    protected $time;

    public function __construct(
        HandlerInterface $handler,
        CacheRepository $cache,
        int $level = Logger::ERROR,
        int $time = 60,
        bool $bubble = true
    ) {
        parent::__construct($handler, 0, $level, $bubble);

        $this->cache = $cache;
        $this->time = $time;
    }

    public function flush()
    {
        if ($this->bufferSize === 0) {
            return;
        }

        $records = [];

        if ($this->time > 0) {

            $expiredAt = now()->addSeconds($this->time);

            foreach ($this->buffer as $record) {

                $cacheKey = 'LogDeduplication:' . sha1($record['message']);

                if ($this->cache->add($cacheKey, 1, $expiredAt)) {
                    $records[] = $record;
                }
            }

            if ($records) {
                $this->handler->handleBatch($records);
            }
        }

        else {
            $this->handler->handleBatch($this->buffer);
        }

        $this->clear();
    }

    public function pushProcessor($callback)
    {
        $this->handler->pushProcessor($callback);

        return $this;
    }

    public function setFormatter(FormatterInterface $formatter)
    {
        $this->handler->setFormatter($formatter);

        return $this;
    }
}
