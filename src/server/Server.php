<?php
namespace Ysp\server;
use Ysp\conf\Config;
/**
 * swoole实现yaf的web_server
 */



class Server
{
    public static $instance;
    public static $http;
    public static $get;
    public static $post;
    public static $header;
    public static $server;
    public static $cookies;
    public static $rawContent;
    private $application;
    private $environment = 'product'; //product OR develop

    private function __construct($conf = array())
    {
        define ('IS_SWOOLE', TRUE);
        define ('HTTP_SERVER',__CLASS__);


        $config = array_merge(Config::getConfig(),$conf);
        $http = new \swoole_http_server($config['host'],$config['port']);
        unset($config['host']);
        unset($config['port']);
        $http->set($config);

        $http->on('WorkerStart', array($this, 'onWorkerStart'));
        $http->on('task', array($this, 'onTask'));
        $http->on('finish', array($this, 'onFinish'));
        $http->on('request', function ($request, $response) use($http) {
            //请求过滤,会请求2次
            if(in_array('/favicon.ico', [$request->server['path_info'],$request->server['request_uri']])){
                return $response->end();
            }

            $self = HTTP_SERVER;
            $self::$header     = isset($request->header)   ? $request->header  : [];
            $self::$get        = isset($request->get)      ? $request->get     : [];
            $self::$post       = isset($request->post)     ? $request->post    : [];
            $self::$cookies    = isset($request->cookies)  ? $request->cookies : [];
            $self::$rawContent = $request->rawContent();
            $self::$http       = $http;

            ob_start();
            try {
                $yaf_request = new \Yaf_Request_Http($request->server['request_uri']);
                $this->application->getDispatcher()->dispatch($yaf_request);
                $result = ob_get_contents();
            } catch (\Yaf_Exception $e ) {
                $result = $e->getMessage();
            }
            ob_end_clean();
            // add Header
            $response->header('Content-Type', 'application/json; charset=utf-8');
            // add cookies
            // set status
            $response->end($result);

        });
        $http->start();
    }


    public function onWorkerStart($serv, $worker_id)
    {
        $errorMsg = array();
        if(!defined('APPLICATION_PATH')){
            $errorMsg[] = 'APPLICATION_PATH未定义';
        }
        if(!is_dir(APPLICATION_PATH.'/application')){
            $errorMsg[] = 'application 文件夹不存在';
        }
        if(!empty($errorMsg)){
            var_dump($errorMsg);
            exit();
        }

        define('APP_PATH', APPLICATION_PATH . '/application/');

        //错误信息将写入swoole日志中
        error_reporting(-1);
        ini_set('display_errors', 1);

        $environment = $this->environment;
        $application = new \Yaf_Application(APPLICATION_PATH . "/conf/application.ini",$environment);

        $this->application = $application;
        $this->application->bootstrap();

        // if ($worker_id >= $serv->setting['worker_num']) {
        //     cli_set_process_title("swoolehttp:task_worker");
        // } else {
        //     cli_set_process_title("swoolehttp:worker");
        // }
    }

    public function onTask($serv, $taskId, $fromId, array $taskdata)
    {
        echo "新的异步任务[来自进程 {$fromId}，当前进程 {$taskId}],data:".json_encode($taskdata).PHP_EOL;
        //$task = TaskLibrary::createTask($taskdata);
    }

    public function onFinish($serv, $taskId, $data)
    {
        # code...
    }

    public static function getInstance($config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }
}
