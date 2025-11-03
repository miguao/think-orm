<?php

declare(strict_types=1);

namespace app\kernel\route\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\kernel\route\provider\RouteAnnotationProvider;
use think\facade\Cache;

class RouteCacheCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('route:cache')
            ->setDescription('Generate route cache with closures for annotation routes');
    }

    protected function execute(Input $input, Output $output): int
    {
        Cache::store('redis')->delete(RouteAnnotationProvider::CACHE_KEY);
        Cache::store('redis')->delete(RouteAnnotationProvider::HASH_KEY);

        RouteAnnotationProvider::register();

        $output->writeln('<info>Route cache generated successfully!</info>');
        return 0;
    }
}