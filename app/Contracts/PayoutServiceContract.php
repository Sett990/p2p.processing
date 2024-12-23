<?php

namespace App\Contracts;

interface PayoutServiceContract
{
    public function getOffersMenu(): array;

    public function makeOffersMenu(): void;
}
