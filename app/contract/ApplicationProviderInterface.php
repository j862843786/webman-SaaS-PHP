<?php

namespace app\contract;

interface ApplicationProviderInterface
{
    public function code(): string;

    public function name(): string;

    public function boot(): void;
}
