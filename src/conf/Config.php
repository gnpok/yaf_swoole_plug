<?php
/**
 * swoole的基本配置
 */

namespace Ysp\conf;

class Config{

    public static function getConfig(){
         return array(
            'host' => '0.0.0.0',
            'port' => 9501,
            'worker_num' => 4,
            'task_worker_num' => 4,
            'daemonize' => false,
            'dispatch_mode' => 3,
            'open_tcp_nodelay' => true,
            'log_file' => dirname(__FILE__).'/../logs/swoole_http_server.log'
        );
    }
}