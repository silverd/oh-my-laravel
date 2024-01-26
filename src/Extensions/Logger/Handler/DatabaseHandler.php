<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Handler;

use Monolog\Level;
use Monolog\LogRecord;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;

/**
 * 日志记录到数据库（扩展 Monolog）
 *
 * @author JiangJian <silverd@sohu.com>
 *
 * @see https://github.com/Seldaek/monolog/blob/master/doc/04-extending.md
 */

class DatabaseHandler extends AbstractProcessingHandler
{
    protected $connection;
    protected $table;
    protected $rotate;

    public function __construct(
        string $table,
        int | Level $level = Level::Info,
        string $rotate = '',
        ?string $connection = null,
        $bubble = true
    ) {
        $this->connection = $connection;
        $this->table      = $table;
        $this->rotate     = $rotate;

        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {

        $table = $this->table . ($this->rotate ? '_' . date($this->rotate) : '');

        try {
            \DB::connection($this->connection)->table($table)->insert([
                'level'      => $record->level,
                'level_name' => $record->level->getName(),
                'channel'    => $record->channel,
                'message'    => $record->message,
                'context'    => jsonEncode($record->context),
                'extra'      => jsonEncode($record->extra),
                'created_at' => $record->datetime->format('Y-m-d H:i:s'),
            ]);
        }
        catch (QueryException $e) {
            // 表不存在
            if ($e->getCode() == '42S02') {
                // 建表后重试
                $this->createTable($table)->write($record);
            }
            else {
                throw $e;
            }
        }
    }

    protected function createTable(string $table)
    {
        // 创建日志表
        if (! \Schema::connection($this->connection)->hasTable($table)) {
            \Schema::connection($this->connection)->create($table, function (Blueprint $tbl) {
                $tbl->increments('id')->unsigned();
                $tbl->integer('level')->unsigned();
                $tbl->string('level_name');
                $tbl->string('channel');
                $tbl->longText('message');
                $tbl->longText('context');
                $tbl->longText('extra');
                $tbl->timestamp('created_at')->nullable();
                $tbl->charset = 'utf8mb4';
                $tbl->collation = 'utf8mb4_unicode_ci';
            });
        }

        return $this;
    }
}
