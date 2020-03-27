# Composer plugin for ThemeFusion

[![Packagist](https://img.shields.io/packagist/v/szepeviktor/composer-theme-fusion.svg?color=239922&style=popout)](https://packagist.org/packages/szepeviktor/composer-theme-fusion)
[![Packagist stats](https://img.shields.io/packagist/dt/szepeviktor/composer-theme-fusion.svg)](https://packagist.org/packages/szepeviktor/composer-theme-fusion/stats)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-239922)](https://github.com/phpstan/phpstan)

A [Composer plugin](https://getcomposer.org/doc/articles/plugins.md)
to load WordPress plugins from [ThemeFusion](https://theme-fusion.com/).

:bulb: Always the latest version is installed, as ThemeFusion does not make other versions available.
Package version locking can only be achieved by local persistent cache, not across hosts or users.

### Installation

This Composer plugin must be installed globally as it adds a virtual package repository.

```shell
composer global require --update-no-dev szepeviktor/composer-theme-fusion
```

### Configuration

Add your token and theme version to your `config.json` (in `$COMPOSER_HOME`).

You find the `token` in WordPress option `fusion_registration`.
Get its value with e.g. WP-CLI `wp option get fusion_registration`.

```json
{
  "config": {
    "theme-fusion": {
      "token": "YOUR THEME-FUSION TOKEN",
      "themeVersion": "YOUR AVADA THEME VERSION, EG. '6.2.2'"
    }
  }
}
```

:bulb: Use the vendor name `theme-fusion`.

### Usage

Once the plugin is installed and configured,
you can simply install any of the **premium** plugins (e.g. `theme-fusion/fusion-core`) as Composer packages.
You find the list plugin slugs in this JSON: `https://updates.theme-fusion.com/?avada_action=get_plugins&avada_version=`

### Behind the scenes

1. This package is a Composer plugin
1. In the `activate` method it creates an `ArrayRepository`
   with package data from ThemeFusion API
1. Package version is queried from ThemeFusion API
1. When installing a package its URL is also queried from ThemeFusion API
