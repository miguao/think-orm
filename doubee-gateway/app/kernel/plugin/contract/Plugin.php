<?php

declare(strict_types=1);

namespace app\kernel\plugin\contract;

interface Plugin
{
    const NAME = 'name';
    const VERSION = 'version';
    const DESCRIPTION = 'description';
    const AUTHOR = 'author';
    const AUTHOR_URL = 'author_url';
    const TYPE = 'type';
}