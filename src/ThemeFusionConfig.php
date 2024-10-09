<?php

declare(strict_types=1);

namespace SzepeViktor\Composer\ThemeFusion;

use Composer\Config;

class ThemeFusionConfig
{
    public const THEMEFUSION_CONFIG = 'theme-fusion';

    /**
     * @var array<array-key, mixed>
     */
    protected $config;

    /**
     * @var bool
     */
    protected $valid;

    public function __construct(Config $composerConfig)
    {
        $this->config = $composerConfig->get(self::THEMEFUSION_CONFIG);

        $this->valid = $this->config !== null
            && \array_key_exists('token', $this->config)
            && \array_key_exists('themeVersion', $this->config);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getToken(): string
    {
        return $this->config['token'];
    }

    public function getThemeVersion(): string
    {
        return $this->config['themeVersion'];
    }
}
