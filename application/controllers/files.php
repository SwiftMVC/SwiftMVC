<?php

/**
 * Example Controller to Acess Files
 *
 * @author Faizan Ayubi
 */
use Shared\Controller as Controller;
use Fonts\Proxy as Proxy;
use Fonts\Types as Types;

class Files extends Controller {

    public function fonts($name) {
        $path = "/fonts";

        if (!file_exists("{$path}/{$name}")) {
            $proxy = new Proxy();

            $proxy->addFontTypes("{$name}", array(
                Types::OTF => "{$path}/{$name}.otf",
                Types::EOT => "{$path}/{$name}.eot",
                Types::TTF => "{$path}/{$name}.ttf"
            ));

            $weight = "";
            $style = "";
            $font = explode("-", $name);

            if (sizeof($font) > 1) {
                switch (strtolower($font[1])) {
                    case "Bold":
                        $weight = "bold";
                        break;
                    case "Oblique":
                        $style = "oblique";
                        break;
                    case "BoldOblique":
                        $weight = "bold";
                        $style = "oblique";
                        break;
                }
            }

            $declarations = "";
            $font = join("-", $font);
            $sniff = $proxy->sniff($_SERVER["HTTP_USER_AGENT"]);
            $served = $proxy->serve($font, $_SERVER["HTTP_USER_AGENT"]);

            if (sizeof($served) > 0) {
                $keys = array_keys($served);
                $declarations .= "@font-face {";
                $declarations .= "font-family: \"{$font}\";";

                if ($weight) {
                    $declarations .= "font-weight: {$weight};";
                }
                if ($style) {
                    $declarations .= "font-style: {$style};";
                }

                $type = $keys[0];
                $url = $served[$type];

                if ($sniff && strtolower($sniff["browser"]) == "ie") {
                    $declarations .= "src: url(\"{$url}\");";
                } else {
                    $declarations .= "src: url(\"{$url}\") format(\"{$type}\");";
                }

                $declarations .= "}";
            }

            header("Content-type: text/css");

            if ($declarations) {
                echo $declarations;
            } else {
                echo "/* no fonts to show */";
            }

            $this->willRenderLayoutView = false;
            $this->willRenderActionView = false;
        } else {
            header("Location: {$path}/{$name}");
        }
    }
}