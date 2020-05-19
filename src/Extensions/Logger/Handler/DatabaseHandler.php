<?php

namespace Silverd\OhMyLaravel\Extensions\Logger\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Database\Schema\Blueprint;

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

    public function __construct(
        string $table,
        int $level = Logger::INFO,
        string $rotate = '',
        ?string $connection = null,
        $bubble = true
    ) {
        $this->connection = $connection;
        $this->table = $table . ($rotate ? '_' . date($rotate) : '');

        // 创建日志表
        $this->checkCreateTable();

        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        \DB::connection($this->connection)->table($this->table)->insert([
            'level'      => $record['level'],
            'level_name' => $record['level_name'],
            'channel'    => $record['channel'],
            'message'    => $record['message'],
            'context'    => jsonEncode($record['context']),
            'extra'      => jsonEncode($record['extra']),
            'created_at' => $record['datetime']->format('Y-m-d H:i:s'),
        ]);
    }

    protected function checkCreateTable()
    {
        // 创建日志表
        if (! \Schema::connection($this->connection)->hastable($this->table)) {
            \Schema::connection($this->connection)->create($this->table, function (Blueprint $table) {
                $table->increments('id')->unsigned();
                $table->integer('level')->unsigned();
                $table->string('level_name');
                $table->string('channel');
                $table->mediumText('message');
                $table->mediumText('context');
                $table->mediumText('extra');
                $table->timestamp('created_at')->nullable();
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            });
        }
    }
}
