<?php
/**
 * User: qian
 * Date: 2019/7/1 16:36
 */

namespace suframe\core\components\register;

use suframe\core\components\Config;
use suframe\core\components\register\Client as ClientAlias;
use suframe\core\components\rpc\RpcPack;
use suframe\core\components\swoole\ProcessTools;
use suframe\core\traits\Singleton;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * 服务定时同步
 * Class SyncServers
 * @package suframe\register\components
 */
class Server
{
    use Singleton;


    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return ClientAlias::getInstance()->reloadServer();
    }

    /**
     * @param $args
     * @return bool
     * @throws \Exception
     */
    public function register($args){
        $config = ClientAlias::getInstance()->reloadServer();
        $path = $args['path'];
        $server = $config->get('servers');
        //rpc服务更新
        $this->registerRpc($path, $args['rpc'] ?? []);

        //唯一key防止重复
        $key = md5($args['ip'] . $args['port']);
        if(isset($server[$path])){
            //已存在
            if($server[$path]->get($key)){
                throw new \Exception('exist');
            }
            $server[$path][$key] = ['ip' => $args['ip'], 'port' => $args['port']];
        } else {
            $server[$path] = [
                $key => ['ip' => $args['ip'], 'port' => $args['port']]
            ];
        }
        try{
            //写入api配置
            ClientAlias::getInstance()->updateLocalFile($server);
        } catch (\Exception $e){
            return false;
        }
        //通知服务更新
        $this->notify(ClientAlias::COMMAND_UPDATE_SERVERS);
        //重启
        ProcessTools::kill();
        return true;
    }

    public function registerRpc($path, $rpc){
        if(!$rpc || (strpos($path, '.') !== false)){
            return false;
        }
        $savePath = SUMMER_APP_ROOT . 'runtime/rpc' . $path;
        $fs = new Filesystem();
        //删除目录
        if(!is_dir($savePath)){
            $fs->remove($savePath);
        }
        //创建目录
        try {
            $fs->mkdir($savePath, '0755');
        } catch (IOExceptionInterface $e) {
            echo "An error occurred while creating your directory at ".$e->getPath();
            return false;
        }

        $methods = '';
        foreach ($rpc as $class => $items) {
            foreach ($items as $item) {
                $parameters = [];
                foreach ($item['parameters'] as $parameter) {
                    $str = isset($parameter['type']) && $parameter['type'] ? $parameter['type'] . ' ' : '';
                    $str .= '$' . $parameter['name'];
                    $str .= isset($parameter['default']) && $parameter['default'] ? ' = ' . $parameter['default'] : '';
                    $parameters[] = $str;
                }
                $parametersStr = implode(', ', $parameters);
                $methods .= <<<EOF

    {$item['doc']}
    public function {$item['name']} ({$parametersStr});
    
EOF;
            }
            $content = <<<EOF
interface {$class}
{
{$methods}
}
EOF;
            $fs->dumpFile($savePath . '/' . $class . '.tpl', $content);
        }
    }

    /**
     * 生成meta文件
     * @return string
     */
    public function buildRpcMeta(){
        $savePath = SUMMER_APP_ROOT . 'runtime/rpc';
        $finder = new Finder();
        $finder->depth('< 2')->name('*.tpl');
        $finder->files()->in($savePath);
        $interfaces = [];
        foreach ($finder as $file) {
            $name = $file->getPathInfo()->getFilename();
            if(!isset($interfaces[$name])){
                $interfaces[$name] = [];
            }
            $key = $file->getFilenameWithoutExtension();
            $interfaces[$name][$key] = $file->getContents();
        }
        $pathName = [];
        $contents = [];
        foreach ($interfaces as $name => $interface) {
            foreach ($interface as $className => $content) {
                $pathName[] = "'/{$name}/{$className}' => \\app\\runtime\\rpc\\{$name}\\{$className}::class,";

            }
            $interfaceContent = implode("\n", $interface);
            $contents[] = <<<EOF
namespace app\\runtime\\rpc\\{$name};

{$interfaceContent}
EOF;

        }
        $contentStr = implode("\n", $contents);
        $pathStr = implode("\n            ", $pathName);

        return <<<EOF
<?php
namespace PHPSTORM_META {
    use suframe\\core\\components\\rpc\\SRpcInterface;
    override( SRpcInterface::route(0),
        map( [
            {$pathStr}
        ]));
}

{$contentStr}
EOF;
    }

    /**
     * 耿直服务更新
     * @return bool
     */
    public function notify($command)
    {
        try {
            $servers = $this->getConfig()->get('servers');
            //通知更新
            $pack = new RpcPack('/summer/client/notify');
            $pack->add('command', $command);
            foreach ($servers as $server) {
                /** @var Config $item */
                foreach ($server as $key => $item) {
                    $client = new \Co\Client(SWOOLE_SOCK_TCP);
                    if (!$client->connect($item['ip'], $item['port'], 0.5)) {
                        exit("connect failed. Error: {$client->errCode}\n");
                    }
                    $client->send($pack->pack());
//                    echo $client->recv();
                    $client->close();
                }
            }
        } catch (\Exception $e) {
        }
    }

}