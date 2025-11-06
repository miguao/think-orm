<?php

declare (strict_types=1);

namespace app\kernel\plugin\handler;

interface Payment
{
    public function create();

    public function async();
}