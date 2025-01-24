<?php

namespace App\Services\Order\Features;

abstract class BaseFeature
{
    abstract public function handle(): mixed;
}
