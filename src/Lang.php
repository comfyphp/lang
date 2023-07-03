<?php

namespace ComfyPHP;

interface LangInterface
{
    public function useLanguage(string $key = null): mixed;
}

class Lang implements LangInterface
{
    public string $root; // root directory
    public string $lang; // selected language
    public Tools\Internal $itool;
    protected \Closure $useLanguageF;

    public function __construct()
    {
        // declarations - class
        $this->itool = new Tools\Internal();

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
        $this->setConfigs();

        // get langs
        $this->getLangs();

        // init useLang function
        $this->initUseLanguage();
    }

    protected function setConfigs(): void
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
            "CONFIG_LANG_COOKIE_TIME" => ["how long will the cookie be valid, default 30 days", "dynamic", "time() + (60 * 60 * 24 * 30)"],
        ];

        $this->itool->checkConfigs($name, $configs);
    }

    protected function getLangs(): void
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

    protected function searchKey(array $data, array $keys): array
    {
        // Get the first key from the array
        $currentKey = array_shift($keys);

        // check current data and key
        if (is_array($data) && array_key_exists($currentKey, $data)) {
            $value = $data[$currentKey];

            // check if value still array
            if (count($keys) > 0 && is_array($value)) {
                // search again
                $result = $this->searchKey($value, $keys);

                // success
                if ($result["message"] !== "failed") {
                    return array(
                        "message" => "success",
                        "data" => $result["data"],
                    );
                }
                // failed
                else {
                    return array(
                        "message" => "failed",
                        "error" => "error: key[" . implode("->", $keys) . "] not found!",
                    );
                }
            }
            // end of array
            else {
                return is_string($value) ?
                // success
                array(
                    "message" => "success",
                    "data" => $value,
                ) :
                // who are u
                array(
                    "message" => "failed",
                    "error" => "key[$currentKey] not found!",
                );
            }
        }

        // result
        return array(
            "message" => "failed",
            "error" => "key[$currentKey] not found!",
        );
    }

    protected function initUseLanguage(): void
    {
        $this->useLanguageF = function (string $key): string{
            // declarations
            $root = $this->root;
            $lang = $this->lang;
            $fallback = $GLOBALS["CONFIG_LANG_FALLBACK"];
            $path = $GLOBALS["CONFIG_LANG_PATH"];
            $debug = $GLOBALS["SYSTEM_DEBUG"];

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

            // target language path is set
            if (file_exists("$targetFilePath")) {
                $json = json_decode(file_get_contents($targetFilePath), true);
            }
            // fallback path
            else if (file_exists("$fallbackFilePath")) {
                $json = json_decode(file_get_contents($fallbackFilePath), true);
            } else {
                return ($debug ? "error: file not found!" : "");
            }

            $value = $this->searchKey($json, $keys);

            // assume target language key not found
            // find fallback language key instead
            if ($value["message"] === "failed") {
                $fb_json = json_decode(file_get_contents($fallbackFilePath), true);
                $fb_value = $this->searchKey($fb_json, $keys);

                // fallback failed
                if ($fb_value["message"] === "failed") {
                    return ($debug ? $fb_value["error"] : "");
                }

                // success
                return $fb_value["data"];
            }

            // success
            return $value["data"];
        };
    }

    public function useLanguage(string $key = null): mixed
    {
        // declarations
        $useLang = $this->useLanguageF;

        // indirect function
        if (!$key) {
            return $useLang;
        }

        // direct function
        return $useLang($key);
    }
}
