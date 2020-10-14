<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class Cli extends Application
{
    /**
     * @var bool
     */
    private bool $commandsRegistered = false;

    /**
     * @var Throwable[]
     */
    private array $registrationErrors = [];

    /**
     * @var string[]
     */
    private array $commandIds;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Cli constructor.
     * @param string $name
     * @param string $version
     * @param string $environment
     * @param array $commandIds
     * @param ContainerInterface $container
     */
    public function __construct(
        string $name,
        string $version,
        string $environment,
        array $commandIds,
        ContainerInterface $container
    ) {
        parent::__construct($name, $version);

        $this->commandIds = $commandIds;
        $this->container = $container;

        $inputDefinition = $this->getDefinition();
        $inputDefinition->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $environment)
        );
        $inputDefinition->addOption(
            new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.')
        );
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an error code
     * @throws Throwable
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $this->registerCommands();

        if ($this->registrationErrors) {
            $this->renderRegistrationErrors($input, $output);
        }

        if ($this->container->has('event_dispatcher')) {
            $this->setDispatcher($this->container->get('event_dispatcher'));
        }

        return parent::doRun($input, $output);
    }

    /**
     * @param Command $command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Throwable
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if (!$command instanceof ListCommand) {
            if ($this->registrationErrors) {
                $this->renderRegistrationErrors($input, $output);
                $this->registrationErrors = [];
            }

            return parent::doRunCommand($command, $input, $output);
        }

        $returnCode = parent::doRunCommand($command, $input, $output);

        if ($this->registrationErrors) {
            $this->renderRegistrationErrors($input, $output);
            $this->registrationErrors = [];
        }

        return $returnCode;
    }

    private function registerCommands(): void
    {
        if ($this->commandsRegistered) {
            return;
        }

        $this->commandsRegistered = true;

        if ($this->container->has('console.command_loader')) {
            $this->setCommandLoader($this->container->get('console.command_loader'));
        }

        foreach ($this->commandIds as $id) {
            try {
                $this->add($this->container->get($id));
            } catch (Throwable $e) {
                $this->registrationErrors[] = $e;
            }
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function renderRegistrationErrors(InputInterface $input, OutputInterface $output): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        (new SymfonyStyle($input, $output))->warning('Some commands could not be registered:');

        foreach ($this->registrationErrors as $error) {
            $this->doRenderThrowable($error, $output);
        }
    }
}
