<?php

declare(strict_types=1);

namespace SzepeViktor\Composer\ThemeFusion;

use Composer\Package\Package;
use Composer\Package\Version\VersionParser;

class ThemeFusionPackage extends Package
{
    public const VENDOR_NAME = 'theme-fusion';

    /**
     * @var string
     */
    protected $fusionName;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var ThemeFusionApi
     */
    protected $api;

    public function __construct(string $fusionName, string $slug, string $prettyVersion, string $distUrl, ThemeFusionApi $api)
    {
        $this->fusionName = $fusionName;
        $this->distUrl = null;
        $this->api = $api;

        $versionParser = new VersionParser();
        parent::__construct(self::VENDOR_NAME . '/' . $slug, $versionParser->normalize($prettyVersion), $prettyVersion);
    }

    public function isDev(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return 'wordpress-plugin';
    }

    /**
     * {@inheritDoc}
     */
    public function getDistType(): ?string
    {
        return 'zip';
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceUrl(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getDistUrl(): string
    {
        if ($this->distUrl !== null && $this->distUrl !== '') {
            return $this->distUrl;
        }

        $this->distUrl = $this->api->getDownloadUrl($this->fusionName);

        return $this->distUrl;
    }

    /**
     * @param string $url
     */
    public function setDistUrl($url): void
    {
        $this->distUrl = $url;
    }

    /**
     * @return bool
     */
    public function isAbandoned()
    {
        return false;
    }
}
