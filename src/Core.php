<?php

namespace suframe\core;

use suframe\core\components\Config;
use suframe\core\components\console\Application;
use suframe\core\components\console\Console;
use suframe\core\components\event\EventManager;
use suframe\core\traits\Singleton;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * summer framework manage
 * manage的主程序就是控制台，通过相应的command和controller完成功能
 * Class Core
 * @package suframe\proxy
 */
abstract class Core
{
    use Singleton;

    /**
     * @var Application
     */
    protected $console;

    /**
     * 初始化
     * @return $this
     */
    public function init()
    {
        //初始化系统配置
        $this->loadConfig();
        //注册事件
        $this->registerListener();
        EventManager::get()->trigger('console.init.before', $this);
        // 注册命令组
        $this->registerCommands();
        EventManager::get()->trigger('console.init.after', $this);
        return $this;
    }

    /**
     * 控制台启动
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function run(): void
    {
        EventManager::get()->trigger('console.run.before', $this);
        $console = $this->getConsole();
        $console->run();
        EventManager::get()->trigger('console.run.after', $this);
    }

    /**
     * 获取console
     * @return Application
     */
    public function getConsole(): Application
    {
        if (!$this->console) {
            $this->console = Console::getInstance()->getApp();
        }
        return $this->console;
    }

    /**
     * 设置console
     * @param Application $console
     */
    public function setConsole(Application $console): void
    {
        $this->console = $console;
    }

    protected $output;

    /**
     * 控制台输出
     * @return mixed
     */
    public function getOutput()
    {
        if ($this->output) {
            return $this->output;
        }
        return $this->output = new ConsoleOutput();
    }

    /**
     * 初始化配置
     */
    protected function loadConfig()
    {
        $config = Config::getInstance();
        // 系统配置
        $config->loadFile(__DIR__ . '/config/config.php');
        // core 配置
        $this->loadCoreConfig();
        // ini配置
        $config->loadFile(SUMMER_APP_ROOT . 'summer.ini');
        // 应用自定义
        $selfConfig = SUMMER_APP_ROOT . 'config/config.php';
        $config->loadFile($selfConfig);
    }

    /**
     * core配置
     * @return Config
     */
    protected function loadCoreConfig(){
        $config = Config::getInstance();
        $path = dirname($this->getReflection()->getFileName());
        $config->loadFile($path . '/config/config.php');
    }

    /**
     * 注册事件
     */
    protected function registerListener()
    {
        $config = Config::getInstance();
        $listeners = $config->get('listener');
        if (!$listeners) {
            return;
        }
        $eventManager = EventManager::get();
        foreach ($listeners as $listener) {
            (new $listener)->attach($eventManager);
        }
    }

    protected function registerCommands(){
        //core命令
        $coreCommands = Config::getInstance()->get('console.coreCommands');
        if($coreCommands){
            foreach ($coreCommands as $coreCommand) {
                $this->getConsole()->registerGroups(
                    __NAMESPACE__ . '\commands\\' . $coreCommand,
                    __DIR__ . '/commands/' . $coreCommand . '/');
            }
        }
        //应用命令
        $path = dirname($this->getReflection()->getFileName());
        if(is_dir($path . '/commands/')){
            $this->getConsole()->registerGroups($this->getReflection()->getNamespaceName() . '\commands', $path . '/commands/');
        }
    }

    protected $reflection;
    protected function getReflection(){
        if($this->reflection){
            return $this->reflection;
        }

        return new \ReflectionClass(get_called_class());
    }
}