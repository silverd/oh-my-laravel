<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Handler;

use Monolog\Level;
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
        int | Level $level = Level::Error,
        int $time = 60,
        int $bufferLimit = 0,
        bool $flushOnOverflow = true,
        bool $bubble = true
    ) {
        parent::__construct($handler, $bufferLimit, $level, $bubble, $flushOnOverflow);

        $this->cache = $cache;
        $this->time = $time;
    }

    public function flush(): void
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

    public function pushProcessor($callback): HandlerInterface
    {
        $this->handler->pushProcessor($callback);

        return $this;
    }

    public function setFormatter(FormatterInterface $formatter): HandlerInterface
    {
        $this->handler->setFormatter($formatter);

        return $this;
    }
}
