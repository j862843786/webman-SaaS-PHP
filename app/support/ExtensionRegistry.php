<?php

namespace app\support;

use app\contract\ApplicationProviderInterface;
use app\contract\PluginProviderInterface;
use InvalidArgumentException;

final class ExtensionRegistry
{
    /** @return array<string, ApplicationProviderInterface> */
    public function applications(): array
    {
        $providers = [];
        $appConfig = config('app_loader', []);
        foreach (($appConfig['applications'] ?? []) as $code => $definition) {
            if (($definition['enabled'] ?? false) !== true || empty($definition['provider'])) {
                continue;
            }

            $provider = $this->make($definition['provider'], ApplicationProviderInterface::class);
            $providers[$code] = $provider;
        }

        return $providers;
    }

    /** @return array<string, PluginProviderInterface> */
    public function plugins(): array
    {
        $providers = [];
        $pluginConfig = config('plugin_loader', []);
        foreach (($pluginConfig['plugins'] ?? []) as $code => $definition) {
            if (($definition['enabled'] ?? false) !== true || empty($definition['provider'])) {
                continue;
            }

            $provider = $this->make($definition['provider'], PluginProviderInterface::class);
            $providers[$code] = $provider;
        }

        return $providers;
    }

    public function bootApplications(): void
    {
        foreach ($this->applications() as $provider) {
            $provider->boot();
        }
    }

    public function bootPlugins(): void
    {
        $pluginConfig = config('plugin_loader', []);
        foreach (($pluginConfig['plugins'] ?? []) as $code => $definition) {
            if (($definition['enabled'] ?? false) !== true || empty($definition['provider'])) {
                continue;
            }

            $provider = $this->make($definition['provider'], PluginProviderInterface::class);
            $provider->boot((array) ($definition['config'] ?? []));
        }
    }

    private function make(string $class, string $contract): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Extension provider %s does not exist.', $class));
        }

        $provider = new $class();
        if (!$provider instanceof $contract) {
            throw new InvalidArgumentException(sprintf('Extension provider %s must implement %s.', $class, $contract));
        }

        return $provider;
    }
}
