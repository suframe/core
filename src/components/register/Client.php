<?php

namespace suframe\core\components\register;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use suframe\core\components\Config;
use Exception;
use suframe\core\traits\Singleton;
use Swoole\Http\Request;
use Symfony\Component\Finder\Finder;
use Zend\Config\Writer\PhpArray;


/**
 * User: qian
 * Date: 2019/7/2 10:59
 */
class Client
{
    use Singleton;
    protected $registerServer;
    const COMMAND_UPDATE_SERVERS = 'updateServers';

    /**
     * @param Request $request
     * @throws Exception
     */
    public function execCommand(Request $request)
    {
        $command = $request->post['command'];
        $method = 'command' . ucfirst($command);
        if(!method_exists($this, $method)){
            throw new Exception('command not found');
        }
        return $this->$method($request);
    }

    protected $registerConfig = [];
    /**
     * @throws Exception
     */
    protected function getRegisterConfig(){
        if(!$this->registerConfig){
            $this->registerConfig = Config::getInstance()->get('registerServer');
            if (!$this->registerConfig) {
                throw new Exception('register server no config');
            }
        }
        return $this->registerConfig;
    }

    /**
     * 注册
     * @param array $serviceConfig
     * @throws Exception
     */
    public function register(array $serviceConfig)
    {
        $serviceConfig['rpc'] = $this->registerRpc();
        $config = $this->getRegisterConfig();
        $client = new \Swoole\Coroutine\Http\Client($config['ip'], $config['port']);
        $client->post('/summer/server/register', $serviceConfig);
        $rs = $client->body;
        $client->close();
        var_dump($rs);
    }

    /**
     * 注册rpc接口
     * @return array|bool
     * @throws \ReflectionException
     */
    public function registerRpc(){
        $rpcPath = Config::getInstance()->get('app.rpcPath', SUMMER_APP_ROOT . 'rpc');
        if(!is_dir($rpcPath)){
            return false;
        }
        $length   = \strlen($rpcPath);
        $finder = new Finder();
        $finder->name('*.php');
        $namespace = Config::getInstance()->get('app.rpcNameSpace', '\app\rpc\\');
        $rpc = [];
        foreach ($finder->in($rpcPath) as $file) {
            $class = $namespace . \substr($file, $length + 1, -4);
            if(!class_exists($class)){
                return false;
            }
            $ref = new ReflectionClass($class);
            $className = $ref->getShortName();
            $rpc[$className] = [];
            $methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                //参数解析
                $parameters = array_map(function (ReflectionParameter $value){
                    $type = $value->hasType() ? $value->getType()->getName() : null;
                    return [
                        'name' => $value->getName(),
                        'require' => !$value->isDefaultValueAvailable(),
                        'type' => $type,
                        'default' => $value->isDefaultValueAvailable() ? $value->getDefaultValue() : null,
                    ];
                }, $method->getParameters());
                $rpc[$className][] = [
                    'name' => $method->getName(),
                    'parameters' => $parameters,
                    'doc' => $method->getDocComment()
                ];
            }
        }
        return $rpc;
    }

    /**
     * 更新
     * @throws Exception
     */
    public function commandUpdateServers()
    {
        $config = $this->getRegisterConfig();
        $client = new \Swoole\Coroutine\Http\Client($config['ip'], $config['port']);
        $client->get('/summer/server/get');
        $rs = $client->body;
        $client->close();
        $rs = json_decode($rs, true);
        $code = $rs['code'] ?? null;
        if($code !== 200){
            throw new Exception('update fail');
        }
        $data = $rs['data'] ?? [];
        $this->updateLocalFile($data);
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function syncRpc(){
        $config = $this->getRegisterConfig();
        $client = new \Swoole\Coroutine\Http\Client($config['ip'], $config['port']);
        $client->post('/summer/server/syncRpc', []);
        $rs = $client->body;
        $client->close();
        $rs = json_decode($rs, true);
        if(!$rs){
            return false;
        }
        if(($rs['code'] == 200) && $rs['data']){
            $file = SUMMER_APP_ROOT . 'config/.phpstorm.meta.php';
            file_put_contents($file, $rs['data']);
            return true;
        }
        return false;
    }

    /**
     * 更新本地文件
     * @param mixed $data
     */
    public function updateLocalFile($data){
        $file = SUMMER_APP_ROOT . 'config/servers.php';
        $writer = new PhpArray();
        $writer->toFile($file, is_object($data) ? $data->toArray() : $data);
        $config = Config::getInstance();
        $config['servers'] = $data;
    }

    /**
     * @return Config
     */
    public function reloadServer(){
        $file = SUMMER_APP_ROOT . 'config/servers.php';
        $config = Config::getInstance();
        unset($config['servers']);
        $config->loadFileByName($file, 'servers');
        return $config;
    }

}