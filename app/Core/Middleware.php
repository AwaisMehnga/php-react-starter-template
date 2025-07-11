<?php

namespace App\Core;

abstract class Middleware
{
    /**
     * Handle the request
     *
     * @param callable $next
     * @return mixed
     */
    abstract public function handle($next);
}
