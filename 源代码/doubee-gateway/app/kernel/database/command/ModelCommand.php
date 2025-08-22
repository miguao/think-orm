<?php

declare(strict_types=1);

namespace app\kernel\database\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class ModelCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('gen:model');
        $this->addArgument('table', Argument::OPTIONAL, 'Which table you want to associated with the Model.');
    }

    protected function execute(Input $input, Output $output): void
    {
        print_r($input->getArguments());
        $class = 'system_user';
        $output->writeln(sprintf('<info>Model %s was created.</info>', $class));
    }
}