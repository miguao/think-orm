<?php

declare (strict_types=1);

namespace app\kernel\plugin\constant;

interface Plugin
{
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const AUTHOR = 'author';
    const VERSION = 'version';
    const TYPE = 'type';

    const FROM = 'from';
    const OPTIONS = 'options';
}