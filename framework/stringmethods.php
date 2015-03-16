<?php

namespace Framework {

    /**
     * StringMethods
     */
    class StringMethods {

        private static $_delimiter = "#";

        private function __construct() {
            # code...
        }

        private function __clone() {
            # code...
        }

        private function _normalize($pattern) {
            return self::$_delimiter . trim($pattern, self::$_delimiter) . self::$_delimiter;
        }

        public function getDelimiter() {
            return self::$_delimiter;
        }

        public function setDelimiter($delimiter) {
            self::$_delimiter = $delimiter;
        }

        public function match($string, $pattern) {
            preg_match_all(self::_normalize($pattern), $string, $matches, PREG_PATTERN_ORDER);
            if (!empty($matches[1])) {
                return $matches[1];
            }

            if (!empty($matches[0])) {
                return $matches[0];
            }

            return null;
        }

        public static function split($string, $pattern, $limit = null) {
            $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
            return preg_split(self::_normalize($pattern), $string, $limit, $flags);
        }

        /**
         * Loops through the characters of a string, replacing them with regular expression 
         * friendly character representations
         * 
         * @param type $string
         * @param type $mask
         * @return type
         */
        public static function sanitize($string, $mask) {
            if (is_array($mask)) {
                $parts = $mask;
            } else if (is_string($mask)) {
                $parts = str_split($mask);
            } else {
                return $string;
            }
            foreach ($parts as $part) {
                $normalized = self::_normalize("\\{$part}");
                $string = preg_replace("{$normalized}m", "\\{$part}", $string);
            }
            return $string;
        }

        /**
         * Eliminates all duplicated characters in a string
         * @param type $string
         * @return type
         */
        public static function unique($string) {
            $unique = "";
            $parts = str_split($string);
            foreach ($parts as $part) {
                if (!strstr($unique, $part)) {
                    $unique .= $part;
                }
            }
            return $unique;
        }

        /**
         * Returns the position of a substring within a larger string, or -1 if the substring isn’t found
         * @param type $string
         * @param type $substring
         * @param type $offset
         * @return type
         */
        public function indexOf($string, $substring, $offset = null) {
            $position = strpos($string, $substring, $offset);
            if (!is_int($position)) {
                return -1;
            } return $position;
        }

        private static $_singular = array(
            "(matr)ices$" => "\\1ix",
            "(vert|ind)ices$" => "\\1ex",
            "^(ox)en" => "\\1",
            "(alias)es$" => "\\1",
            "([octop|vir])i$" => "\\1us",
            "(cris|ax|test)es$" => "\\1is",
            "(shoe)s$" => "\\1",
            "(o)es$" => "\\1",
            "(bus|campus)es$" => "\\1",
            "([m|l])ice$" => "\\1ouse",
            "(x|ch|ss|sh)es$" => "\\1",
            "(m)ovies$" => "\\1\\2ovie",
            "(s)eries$" => "\\1\\2eries",
            "([^aeiouy]|qu)ies$" => "\\1y",
            "([lr])ves$" => "\\1f",
            "(tive)s$" => "\\1",
            "(hive)s$" => "\\1",
            "([^f])ves$" => "\\1fe",
            "(^analy)ses$" => "\\1sis",
            "((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$" => "\\1\\2sis",
            "([ti])a$" => "\\1um",
            "(p)eople$" => "\\1\\2erson",
            "(m)en$" => "\\1an",
            "(s)tatuses$" => "\\1\\2tatus",
            "(c)hildren$" => "\\1\\2hild",
            "(n)ews$" => "\\1\\2ews",
            "([^u])s$" => "\\1"
        );
        
        private static $_plural = array(
            "^(ox)$" => "\\1\\2en",
            "([m|l])ouse$" => "\\1ice",
            "(matr|vert|ind)ix|ex$" => "\\1ices",
            "(x|ch|ss|sh)$" => "\\1es",
            "([^aeiouy]|qu)y$" => "\\1ies",
            "(hive)$" => "\\1s",
            "(?:([^f])fe|([lr])f)$" => "\\1\\2ves",
            "sis$" => "ses",
            "([ti])um$" => "\\1a",
            "(p)erson$" => "\\1eople",
            "(m)an$" => "\\1en",
            "(c)hild$" => "\\1hildren",
            "(buffal|tomat)o$" => "\\1\\2oes",
            "(bu|campu)s$" => "\\1\\2ses",
            "(alias|status|virus)" => "\\1es",
            "(octop)us$" => "\\1i",
            "(ax|cris|test)is$" => "\\1es",
            "s$" => "s",
            "$" => "s"
        );

        /**
         * Converts a plural string to singular
         * @param type $string
         * @return type
         */
        public static function singular($string) {
            $result = $string;
            foreach (self::$_singular as $rule => $replacement) {
                $rule = self::_normalize($rule);
                if (preg_match($rule, $string)) {
                    $result = preg_replace($rule, $replacement, $string);
                    break;
                }
            } return $result;
        }

        /**
         * Converts a singular string to plural
         * @param type $string
         * @return type
         */
        function plural($string) {
            $result = $string;
            foreach (self::$_plural as $rule => $replacement) {
                $rule = self::_normalize($rule);
                if (preg_match($rule, $string)) {
                    $result = preg_replace($rule, $replacement, $string);
                    break;
                }
            } return $result;
        }

    }

}