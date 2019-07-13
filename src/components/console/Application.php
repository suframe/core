<?php
namespace suframe\core\components\console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

class Application extends \Symfony\Component\Console\Application
{

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
        ]);
    }

    /**
     * auto register controllers from a dir.
     * @param string $namespace
     * @param string $basePath
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function registerGroups(string $namespace, string $basePath): self
    {
        $length   = \strlen($basePath);
        $finder = new Finder();
        $finder->name('*Command.php');
        foreach ($finder->in($basePath) as $file) {
            $class = $namespace . '\\' . \substr($file, $length, -4);
            $this->add(new $class);
        }
        return $this;
    }


}