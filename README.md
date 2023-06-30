# ComfyPHP Multiple Languages Extension

This is an extension for ComfyPHP framework to enable the function using multiple languages.

## Default & Recommended Directory Structure

-   `src` directory (default) <br/>
    Where the source files for editing reside. <br/><br/>
    -   `langs` directory (default) <br/>
        Store all the languages related source code. <br/><br/>
        -   `en` directory (optional) <br/>
            English translation folder. <br/><br/>
            -   `index.json` language file <br/>
                English translation files. <br/><br/>
        -   `zh-Hant` directory (optional) <br/>
            Traditional Chinese translation folder. <br/><br/>
            -   `index.json` language file <br/>
                Traditional Chinese translation files. <br/><br/>

## Before Using it

As this is an extension for ComfyPHP, All dependencies required in ComfyPHP and ComfyPHP is needed to use this extension.

## Download / Install

To use this extension, you can install it with Composer.

```bash
composer require comfyphp/lang
```

## Usage

ComfyPHP will search for all the languages base on the `CONFIG_LANG_PATH` settings in `comfy.lang.config.php`.

You can add the following line into somewhere and import it into every files later, here we take `src/pages/_init.php` for example:

```php
$lang = new ComfyPHP\Lang();
```

Create separate JSON files for each language you want to support. And Place these files into the languages folder. For example, create the following files:

```php
// src/langs/en/index.json
{
    "hello": "Hello!"
}
// src/langs/en/special.json
{
    "setting": {
        "title": "Settings"
        "info": "This is the Settings page."
    }
}
```

In the files where you want to use the multiple languages extension, add the following code to require the file which you initialized the lang extension and enable the function to use those languages:

```php
$root = $GLOBALS["ROOT"];
$pagePath = $GLOBALS["CONFIG_PAGE_PATH"];
require_once "$root/$pagePath/_init.php";
$t = $lang->useLanguage();
```

Within the body of your code, you can now access the language strings.

```php
echo $t("hello");
echo $t("special:setting.info");
```

## License

This project is MIT licensed, you can find the license file [here](./LICENSE).
