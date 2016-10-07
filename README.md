#WP Must-Use Plugin Loader

[![Build Status](https://travis-ci.org/lkwdwrd/wp-muplugin-loader.svg?branch=master)](https://travis-ci.org/lkwdwrd/wp-muplugin-loader)

Managing plugins using the [Composer Installers](https://github.com/composer/installers) library works remarkably well. However, it's handling of MU plugins leaves something to be desired.

WordPress MU (must use) Plugins are files that are placed in the `wp-content/mu-plugins/` folder. These files are loaded automatically. The problem is when a plugin is actually inside a folder. WordPress will only load .php files and doesn't drop into any directories. When the Composer Installers plugin runs, it always puts the repo into a nice contained folder. This means the Composer Installers MU plugins never actually run!

There are manual ways around this that work fine, but I want to get away from any manual steps when running the install. No extra files, just run `composer install` or `composer update` and have it work. That is what the WP Must-Use Plugin Loader does.

## Usage Instructions

In your project's `composer.json` file, require this package.

```json
"require": {
	"composer/installers": "~1.2.0",
	"johnpbloch/wordpress": "*",
	"lkwdwrd/wp-muplugin-loader": "~1.0.0",
}
```
Make sure in the `extras` of your `composer.json` you have your mu-plugins path defined.

```json
"extra": {
	"installer-paths": {
		"app/wp-content/themes/{$name}": [
			"type:wordpress-theme"
		],
		"app/wp-content/plugins/{$name}": [
			"type:wordpress-plugin"
		],
		"app/wp-content/mu-plugins/{$name}": [
			"type:wordpress-muplugin"
		]
	},
	"wordpress-install-dir": "app/wp"
}
```

And that's it.

When Composer dumps it's autoload file, a file called `mu-require.php` will be placed into your mu-plugins folder. When WordPress loads this file as an MU plugin, it will find all of the plugins in folders in your MU plugins directory and include those as well.

## Forcing MU Plugins

Usually when you are using MU plugins, you have some 'normal' WordPress plugins that you want to always be active. They are not always MU-Plugins, though, so it makes no sense to put the `"type": "wordpress-muplugin"` in the `composer.json` file. WP Must-Use Plugin Loader allows you to override the type from `wordpress-plugin` to `wordpress-muplugin` as needed.

To do this, define a `"force-mu"` key in `"extra"` of your `composer.json` file. This key should hold an array of slugs for plugins to force into Must-Use status.

This is compatible with [WPackagist](https://wpackagist.org/). When adding plugins from WPackagist use the plugin's normal slug, not the wp-packagist version.

```json
"require": {
	"johnpbloch/wordpress": "*",
	"lkwdwrd/wp-muplugin-loader": "~1.0.0",
	"wpackagist-plugin/rest-api": "*"
},
"extra": {
	"force-mu": [
		"rest-api"
	],
	"installer-paths": {
		"app/wp-content/themes/{$name}": [
			"type:wordpress-theme"
		],
		"app/wp-content/plugins/{$name}": [
			"type:wordpress-plugin"
		],
		"app/wp-content/mu-plugins/{$name}": [
			"type:wordpress-muplugin"
		]
	},
	"wordpress-install-dir": "app/wp"
}
```

When the `rest-api` plugin is installed, instead of going in the normal plugins folder, it will be pushed over to the mu-plugins folder and loaded automatically with other Must-Use Plugins.
