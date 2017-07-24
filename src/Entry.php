<?php
/**
 * yaf_swoole的入口,ysp的意思是yaf+swoole_plug[yaf+swoole webserver的插件]
 */
namespace Ysp;
use Ysp\server\Server;

class Entry{

    public static function startServer(){
       Server::getInstance();
    }
}
