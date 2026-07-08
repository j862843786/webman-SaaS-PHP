<?php

namespace app\contract;

interface PluginProviderInterface
{
    public function code(): string;

    public function name(): string;

    public function version(): string;

    public function boot(array $config = []): void;
}
