<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Monolog\LogRecord;

class SimpleJsonFormatter extends NormalizerFormatter
{
    public function format(LogRecord $record): string
    {
        $normalized = $this->normalize($record);

        return $this->toJson($normalized['context'] ?? []) . PHP_EOL;
    }

    public function formatBatch(array $records)
    {
        $string = '';

        foreach ($records as $record) {
            $string .= $this->format($record);
        }

        return $string;
    }
}
