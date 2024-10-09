<?php

declare(strict_types=1);

namespace SzepeViktor\Composer\ThemeFusion;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Repository\ArrayRepository;

/**
 * Composer Plugin for embedded ThemeFusion plugins.
 */
class ThemeFusionPlugin implements PluginInterface
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @var ThemeFusionConfig
     */
    protected $config;

    /**
     * @var ThemeFusionApi
     */
    protected $api;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
        $composerConfig = $composer->getConfig();
        $this->config = new ThemeFusionConfig($composerConfig);
        if (! $this->config->isValid()) {
		    error_log("Invalid config");
            return;
        }

        $this->api = new ThemeFusionApi($io, $composerConfig,
            $this->config->getToken(), $this->config->getThemeVersion());
        $rm = $composer->getRepositoryManager();
        $rm->addRepository($this->generateRepository());
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    protected function generateRepository(): ArrayRepository
    {
        $api = $this->api;

        return new ArrayRepository(\array_map(
            static function ($plugin) use ($api) {
                return new ThemeFusionPackage(
                    $plugin['name'],
                    $plugin['slug'],
                    $plugin['version'],
                    $plugin['url'],
                    // TODO There is no DI container in Composer
                    $api
                );
            },
            $api->getPlugins()
        ));
    }
}
