# ComfyPHP Multiple Languages Extension

This is an extension for ComfyPHP framework to enable the function using multiple languages.

## Default & Recommended Directory Structure

```
├── src
│   └── langs
│       ├── en
│       │   └── index.json
│       ├── zh-Hans
│       │   └── index.json
│       └── zh-Hant
│           └── index.json
└── comfy.lang.config.php
```

## Before Using it

As this is an extension for ComfyPHP, All dependencies required in ComfyPHP and ComfyPHP itself is needed to use this extension.

## Download / Install

To use this extension, you can install it with Composer.

```bash
composer require comfyphp/lang
```

## Usage

### Initialize

ComfyPHP will search for all the languages base on the `CONFIG_LANG_PATH` settings in `comfy.lang.config.php`.

You can add the following line into somewhere and import it into every files later, here we take `src/pages/_init.php` for example:

```php
$lang = new ComfyPHP\Lang();
```

### Create JSON

Create separate JSON files for each language you want to support. And Place these files into the languages folder. For example, create the following files:

`src/langs/en/index.json`:

```json
{
    "hello": "Hello!"
}
```

`src/langs/en/special.json`:

```json
{
    "setting": {
        "title": "Settings",
        "info": "This is the Settings page."
    }
}
```

### Usage

In the files where you want to use the multiple languages extension, add the following code to require the file which you initialized the lang extension and enable the function to use those languages:

```php
$root = $GLOBALS["ROOT"];
$pagePath = $GLOBALS["CONFIG_PAGE_PATH"];
require_once "$root/$pagePath/_init.php";
$l = $lang->useLanguage();
```

You can now use language strings in your code. When you write `$l("hello")`, the extension will look for the key `hello` in the `index.json` file. If you write `$l("special:setting.info")`, the extension will search for the key `info` under the `setting` key in the `special.json` file.

```php
echo $l("hello");
echo $l("special:setting.info");
```

## Reserved Variables of the Extension

```php
$GLOBALS["CONFIG_LANG_PATH"];
$GLOBALS["CONFIG_LANG_PROVIDER"];
$GLOBALS["CONFIG_LANG_FALLBACK"];
$GLOBALS["CONFIG_LANG_PARAM"];
$GLOBALS["CONFIG_LANG_PARAM_NAME"];
$GLOBALS["CONFIG_LANG_COOKIE"];
$GLOBALS["CONFIG_LANG_COOKIE_NAME"];
$GLOBALS["CONFIG_LANG_COOKIE_DOMAIN"];
$GLOBALS["CONFIG_LANG_COOKIE_TIME"];
```

## License

This project is MIT licensed, you can find the license file [here](./LICENSE).
