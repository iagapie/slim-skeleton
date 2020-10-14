<?php

declare(strict_types=1);

namespace App\Ui\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class HelloCommand extends Command
{
    protected function configure()
    {
        $this->setDescription('Hello command.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $style->note('Hello Cli!!!');

        return self::SUCCESS;
    }
}
