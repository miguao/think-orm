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
        Cache::delete(RouteAnnotationProvider::CACHE_KEY);
        Cache::delete(RouteAnnotationProvider::HASH_KEY);

        RouteAnnotationProvider::register();

        $output->writeln('<info>Route cache generated successfully!</info>');
        return 0;
    }
}