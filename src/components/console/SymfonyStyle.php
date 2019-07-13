<?php


namespace suframe\core\components\console;


class SymfonyStyle extends \Symfony\Component\Console\Style\SymfonyStyle
{

    /**
     * {@inheritdoc}
     */
    public function success($message)
    {
        $this->block($message, 'OK', 'fg=green;', ' ', false);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message)
    {
        $this->block($message, 'ERROR', 'fg=red;', ' ', false);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message)
    {
        $this->block($message, 'WARNING', 'fg=yellow;', ' ', false);
    }

}