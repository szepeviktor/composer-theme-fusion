<?php

declare(strict_types=1);

namespace SzepeViktor\Composer\ThemeFusion;

use Composer\Config;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Util\RemoteFilesystem;

class ThemeFusionApi
{
    public const API_DOMAIN = 'updates.theme-fusion.com';
    public const API_BASE_URL = 'https://updates.theme-fusion.com';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var RemoteFilesystem
     */
    protected $remoteFilesystem;

    /**
     * @var string
     */
    protected $themeVersion = '5.9.0'; // FIXME

    public function __construct(IOInterface $io, Config $config, string $token)
    {
        $this->remoteFilesystem = Factory::createRemoteFilesystem($io, $config);
        $this->token = $token;
    }

    /**
     * @return array<int, array{name: string, slug: string, version: string, url: string}>
     */
    public function getPlugins(): array
    {
        $plugins = [];

        $responseBody = $this->remoteFilesystem->getContents(
            self::API_DOMAIN,
            self::API_BASE_URL . '/?' . \http_build_query(['avada_action' => 'get_plugins', 'avada_version' => $this->themeVersion]),
            false // Hide progress indicator
        );

        if ($this->remoteFilesystem->findStatusCode($this->remoteFilesystem->getLastHeaders()) === 200) {
            $pluginData = \json_decode($responseBody, true);
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
            ]);
    }
    /**
     * @return array{string, int}
     */
    protected function getNonce(string $name): array
    {
        $responseBody = $this->remoteFilesystem->getContents(
            self::API_DOMAIN,
            self::API_BASE_URL . '/?' . \http_build_query(
            [
                'token' => $this->token,
                'avada_action' => 'request_download',
                'item_name' => $name,
                'ver' => $this->themeVersion,
            ]),
            false
        );

        if ($this->remoteFilesystem->findStatusCode($this->remoteFilesystem->getLastHeaders()) !== 200) {
            return ['', 0];
        }

        // Nonce and timestamp
        $nonceData = \explode('|', $responseBody);

        return [$nonceData[0], (int)$nonceData[1]];
    }
}
