<?php

declare(strict_types=1);

namespace SzepeViktor\Composer\ThemeFusion;

use Composer\Config;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Util\HttpDownloader;

class ThemeFusionApi
{
    public const API_BASE_URL = 'https://updates.theme-fusion.com';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var HttpDownloader
     */
    protected $httpDownloader;

    /**
     * @var string
     */
    protected $themeVersion;

    public function __construct(IOInterface $io, Config $config, string $token, string $themeVersion)
    {
        $this->httpDownloader = Factory::createHttpDownloader($io, $config);
        $this->token = $token;
        $this->themeVersion = $themeVersion;
    }

    /**
     * @return array<int, array{name: string, slug: string, version: string, url: string}>
     */
    public function getPlugins(): array
    {
        $plugins = [];

        $apiResponse = $this->httpDownloader->get(
            self::API_BASE_URL . '/?' . \http_build_query(['avada_action' => 'get_plugins', 'avada_version' => $this->themeVersion])
        );

        if ($apiResponse->getStatusCode() === 200) {
            $pluginData = \json_decode($apiResponse->getBody(), true);
            /** @var array<array<string>> $pluginData */
            foreach ($pluginData as $plugin) {
                // Non-premium plugins have no version
                if (! $plugin['premium']) {
                    continue;
                }

                $plugins[] = [
                    'name' => $plugin['plugin_name'],
                    'slug' => $plugin['slug'],
                    'version' => $plugin['latest_version'],
                    'url' => $plugin['external_url'],
                ];
            }
        }

        return $plugins;
    }

    public function getDownloadUrl(string $name): string
    {
        list($nonce, $timestamp) = $this->getNonce($name);

        // TODO If response is HTTP/302 download URL is in "Location" header

        return self::API_BASE_URL . '/?' . \http_build_query(
            [
                'nonce' => $nonce,
                't' => $timestamp,
                'avada_action' => 'get_download',
                'item_name' => $name,
                'ver' => $this->themeVersion,
                'token' => $this->token,
            ]);
    }
    /**
     * @return array{string, int}
     */
    protected function getNonce(string $name): array
    {
        $apiResponse = $this->httpDownloader->get(
            self::API_BASE_URL . '/?' . \http_build_query(
            [
                'token' => $this->token,
                'avada_action' => 'request_download',
                'item_name' => $name,
                'ver' => $this->themeVersion,
            ])
        );

    if ($apiResponse->getStatusCode() !== 200) {
        error_log('Theme Fusion API error:'. (string)$apiResponse->getStatusCode());
            return ['', 0];
    }

    $nonceData = \explode('|', $apiResponse->getBody());

        return [$nonceData[0], (int)$nonceData[1]];
    }
}
