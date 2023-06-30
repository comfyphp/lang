<?php

namespace ComfyPHP;

class Lang
{
    public string $root; // root directory
    public string $lang; // selected language
    public Core $core;
    public Tool $tool;

    public function __construct()
    {
        // declarations - class
        $this->core = new Core();
        $this->tool = new Tool();

        // declarations
        $this->root = $GLOBALS["ROOT"];

        $GLOBALS["CONFIG_LANG_PATH"] = "src/langs";
        $GLOBALS["CONFIG_LANG_PROVIDER"] = ["en", "zh-Hant"];
        $GLOBALS["CONFIG_LANG_FALLBACK"] = "en";
        $GLOBALS["CONFIG_LANG_PARAM"] = false;
        $GLOBALS["CONFIG_LANG_PARAM_NAME"] = "lang";
        $GLOBALS["CONFIG_LANG_COOKIE"] = false;
        $GLOBALS["CONFIG_LANG_COOKIE_NAME"] = "lang";
        $GLOBALS["CONFIG_LANG_COOKIE_DOMAIN"] = "";
        $GLOBALS["CONFIG_LANG_COOKIE_TIME"] = time() + 3600 * 24 * 30;

        // init $lang
        $this->lang = $GLOBALS["CONFIG_LANG_FALLBACK"];

        // get configs
        $this->getConfigs();

        // get langs
        $this->getLangs();
    }

    private function getConfigs()
    {
        $name = "comfy.lang.config.php";
        $configs = [
            "CONFIG_LANG_PATH" => ["multiple languages location", "string", "src/langs"],
            "CONFIG_LANG_PROVIDER" => ["which languages to provide", "array", ["en", "zh-Hant"]],
            "CONFIG_LANG_FALLBACK" => ["fallback language if any issue", "string", "en"],
            "CONFIG_LANG_PARAM" => ["allow user to change language by URL, such as ?lang=en", "boolean", true],
            "CONFIG_LANG_PARAM_NAME" => ["name of the parameter that will be read by ComfyPHP", "string", "lang"],
            "CONFIG_LANG_COOKIE" => ["save language preference to cookie", "boolean", true],
            "CONFIG_LANG_COOKIE_NAME" => ["cookie name that store the language", "string", "lang"],
            "CONFIG_LANG_COOKIE_DOMAIN" => ["cookie domain that store the language, default blank for localhost", "string", ""],
            "CONFIG_LANG_COOKIE_TIME" => ["how long will the cookie be valid, default 1 month", "dynamic", "time() + 3600 * 24 * 30"],
        ];

        $this->core->setConfigs($name, $configs);
    }

    private function getLangs()
    {
        // lang provider
        $provider = $GLOBALS["CONFIG_LANG_PROVIDER"];

        // param
        $paramConfig = $GLOBALS["CONFIG_LANG_PARAM"];
        $paramName = $GLOBALS["CONFIG_LANG_PARAM_NAME"];

        // cookie
        $cookieConfig = $GLOBALS["CONFIG_LANG_COOKIE"];
        $cookieName = $GLOBALS["CONFIG_LANG_COOKIE_NAME"];
        $cookieDomain = $GLOBALS["CONFIG_LANG_COOKIE_DOMAIN"];
        $cookieTime = $GLOBALS["CONFIG_LANG_COOKIE_TIME"];

        // param
        if (
            $paramConfig &&
            isset($paramName) &&
            isset($_GET[$paramName]) &&
            in_array($_GET[$paramName], $provider)
        ) {
            $this->lang = htmlspecialchars($_GET[$paramName]);
        }
        // cookie
        elseif (
            $cookieConfig &&
            isset($cookieName) &&
            isset($_COOKIE[$cookieName]) &&
            in_array($_COOKIE[$cookieName], $provider)
        ) {
            $this->lang = htmlspecialchars($_COOKIE[$cookieName]);
        }

        // set cookie
        $cookieConfig && setcookie($cookieName, $this->lang, $cookieTime, "/", $cookieDomain);
    }

    public function useLanguage()
    {
        return function ($key) {
            // declarations
            $root = $this->root;
            $lang = $this->lang;
            $fallback = $GLOBALS["CONFIG_LANG_FALLBACK"];
            $path = $GLOBALS["CONFIG_LANG_PATH"];

            // $targetFile = "settings" etc...
            // $keys = ["home", "title"];

            $targeyAndKeys = explode(":", $key);
            $targetFile = $targeyAndKeys[0];

            // target file is set
            // settings:home.title
            if (isset($targeyAndKeys[1])) {
                $keys = explode(".", $targeyAndKeys[1]);
                $targetFilePath = "$root/$path/$lang/$targetFile.json";
                $fallbackFilePath = "$root/$path/$fallback/$targetFile.json";
            }
            // target file is not set
            // home.title
            else {
                $keys = explode(".", $targeyAndKeys[0]);
                $targetFilePath = "$root/$path/$lang/index.json";
                $fallbackFilePath = "$root/$path/$fallback/index.json";
            }

            if (file_exists("$targetFilePath")) {
                $json = file_get_contents("$targetFilePath");
                $json_data = json_decode($json, true);
            } else if (file_exists("$fallbackFilePath")) {
                $json = file_get_contents("$fallbackFilePath");
                $json_data = json_decode($json, true);
            }

            return $this->searchKey($json_data, $keys);
        };
    }

    private function searchKey(array $data, array $keys): string
    {
        // declarations
        $debug = $GLOBALS["SYSTEM_DEBUG"];
        // Get the first key from the array
        $currentKey = array_shift($keys);

        if (is_array($data) && array_key_exists($currentKey, $data)) {
            $value = $data[$currentKey];

            if (count($keys) > 0 && is_array($value)) {
                $result = $this->searchKey($value, $keys);
                return is_string($result) ? $result : ($debug ? "key:$result not found" : "");
            } else {
                return is_string($value) ? $value : ($debug ? "key:$value not found" : "");
            }
        }

        return ($debug ? "key:$currentKey not found" : "");
    }
}
