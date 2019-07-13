<?php


namespace suframe\core\components\console;

use suframe\core\commands\SummerCommand;
use suframe\core\traits\Singleton;

class Console
{
    use Singleton;
    /**
     * @var Application
     */
    private $app;

    /**
     * 获取控制台应用
     * @return Application
     */
    public function getApp()
    {
        if (!$this->app) {
            $this->app = new Application();
        }
        $default = new SummerCommand();
        $this->app->add($default);
        $this->app->setDefaultCommand($default->getName());
        return $this->app;
    }

}