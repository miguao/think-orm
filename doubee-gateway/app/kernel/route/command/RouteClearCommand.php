<?php

declare(strict_types=1);

namespace app\kernel\route\command;

use app\kernel\route\provider\RouteAnnotationProvider;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Cache;

class RouteClearCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('route:clear')
            ->setDescription('Clear annotation route cache');
    }

    protected function execute(Input $input, Output $output): int
    {
        Cache::store('redis')->delete(RouteAnnotationProvider::CACHE_KEY);
        Cache::store('redis')->delete(RouteAnnotationProvider::HASH_KEY);

        $output->writeln('<info>Route cache cleared successfully!</info>');
        return 0;
    }
}