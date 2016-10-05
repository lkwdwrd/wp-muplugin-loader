#WP Must-Use Plugin Loader

Managing plugins using the [Composer Installers](https://github.com/composer/installers) library works remarkably well. However, it's handling of MU plugins leaves something to be desired.

WordPress MU (must use) Plugins are files that are placed in the `wp-content/mu-plugins/` folder. These files are loaded automatically. The problem is when a plugin is actually inside a folder. WordPress will only load .php files and doesn't drop into any directories. When the Composer Installers plugin runs, it always puts the repo into a nice contained folder. This means the Composer Installers MU plugins never actually run!

There are manual ways around this that work fine, but I want to get away from any manual steps when running the install. No extra files, just run `composer install` or `composer update` and have it work. That is what the WP Must-Use Plugin Loader does.

## Usage Instructions

In your project's `composer.json` file, require this package.

```json
"require": {
	"composer/installers": "~1.2.0",
	"johnpbloch/wordpress": "*",
	"lkwdwrd/wp-mu-plugin-loader": "~1.0.0",
}
```
Make sure in the `extras` of your `composer.json` you have your mu-plugins path defined.

```json

```

That's it. When Composer
