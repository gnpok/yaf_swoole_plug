<?php
namespace Ysp\server;
/**
 * swoole实现yaf的web_server
 */

define ('DS', DIRECTORY_SEPARATOR);
define ('ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define ('CONF_PATH', dirname(__FILE__).'/../conf/');

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

    private function __construct()
    {
        define('IS_SWOOLE', TRUE);
        define('HTTP_SERVER',__CLASS__);

        $config = new \Yaf_Config_Ini(CONF_PATH. 'swoole.ini',$this->environment);
        $configArr = $config->toArray();
        function p($arr){
            echo "<pre>",print_r($arr),"</pre>";
        }
        $config = $configArr['swoole'];
        extract($config);

        $http = new \swoole_http_server($host, $port);
        $http->set(array(
            'worker_num'                => $worker_num,         //worker进程数
            'task_worker_num'           => $task_worker_num,    //task_worker进程数
            'daemonize'                 => $daemonize,
            'dispatch_mode'             => $dispatch_mode,
            'open_tcp_nodelay'          => $open_tcp_nodelay,
            'open_tcp_keepalive'        => '',
            'tcp_defer_accept'          => '',
            'log_file'                  => ROOT_PATH.'/logs/swoole_http_server.log',
        ));

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

            try {
                $yaf_request = new \Yaf_Request_Http($request->server['request_uri']);
                $yaf_response = $this->application->getDispatcher()->dispatch($yaf_request);
                $json_result = $yaf_response->getBody();
            } catch (\Exception $e) {
                $result = array();
                $result['code'] = $e->getCode();
                $result['msg'] = $e->getMessage();
                $json_result = json_encode($result);
            }
            $response->header('Content-Type', 'application/json; charset=utf-8');
            $response->end($json_result);
        });

        $http->start();
    }

    public function onWorkerStart($serv, $worker_id)
    {
        if(!defined('APPLICATION_PATH')){
            echo 'APPLICATION_PATH未定义';
            return false;
        }
        define('APP_PATH', APPLICATION_PATH . '/application/');

        //错误信息将写入swoole日志中
        error_reporting(-1);
        ini_set('display_errors', 1);

        $environment = $this->environment;
        $application = new \Yaf_Application(APPLICATION_PATH . "/conf/application.ini",$environment);

        $this->application = $application;
        $this->application->bootstrap();

        if ($worker_id >= $serv->setting['worker_num']) {
            //cli_set_process_title("swoolehttp:task_worker");
        } else {
            //cli_set_process_title("swoolehttp:worker");
        }
    }

    public function onTask($serv, $taskId, $fromId, array $taskdata)
    {
        echo "新的异步任务[来自进程 {$fromId}，当前进程 {$taskId}],data:".json_encode($taskdata).PHP_EOL;
        //$task = TaskLibrary::createTask($taskdata);
//        var_dump($task);
    }

    public function onFinish($serv, $taskId, $data)
    {
        # code...
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}