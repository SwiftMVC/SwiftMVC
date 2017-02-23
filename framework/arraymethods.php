<?php

namespace Framework {

    /**
     * Utility methods for working with the basic data types we ﬁnd in PHP
     */
    class ArrayMethods {

        private function __construct() {
            # code...
        }

        private function __clone() {
            //do nothing
        }

        /**
         * Useful for converting a multidimensional array into a unidimensional array.
         * 
         * @param type $array
         * @param type $return
         * @return type
         */
        public static function flatten($array, $return = array()) {
            foreach ($array as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $return = self::flatten($value, $return);
                } else {
                    $return[] = $value;
                }
            }
            return $return;
        }

        public static function first($array) {
            if (sizeof($array) == 0) {
                return null;
            }

            $keys = array_keys($array);
            return $array[$keys[0]];
        }

        public static function last($array) {
            if (sizeof($array) == 0) {
                return null;
            }

            $keys = array_keys($array);
            return $array[$keys[sizeof($keys) - 1]];
        }

        public static function toObject($array) {
            $result = new \stdClass();
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $result->{$key} = self::toObject($value);
                } else {
                    $result->{$key} = $value;
                }
            } return $result;
        }

        /**
         * Removes all values considered empty() and returns the resultant array
         * @param type $array
         * @return type the resultant array
         */
        public static function clean($array) {
            return array_filter($array, function ($item) {
                return !empty($item);
            });
        }

        /**
         * Returns an array, which contains all the items of the initial array, after they have been trimmed of all whitespace.
         * @param type $array
         * @return type array trimmed
         */
        public static function trim($array) {
            return array_map(function ($item) {
                return trim($item);
            }, $array);
        }

        /**
         * Rearranges the array keys
         */
        public static function reArray(&$array) {
            $file_ary = array();
            $file_keys = array_keys($array);
            $file_count = count($array[$file_keys[0]]);
            
            for ($i = 0; $i < $file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $array[$key][$i];
                }
            }

            return $file_ary;
        }

        public static function copy(&$from, &$to) {
            foreach ($from as $key => $value) {
                $to[$key] = $value;
            }
        }

        public static function counter(&$arr, $key, $count) {
            if (!array_key_exists($key, $arr)) {
                $arr[$key] = 0;
            }
            $arr[$key] += $count;
        }

        public static function add(&$from, &$to) {
            foreach ($from as $key => $value) {
            	if (!is_array($to)) {
            		$to = [];
            	}
                if (!array_key_exists($key, $to)) {
                    $to[$key] = 0;
                }

                if (is_numeric($value)) {
                    $to[$key] += $value;
                }
            }
        }

        public static function topValues($arr, $count = 10, $order = 'desc') {
            $result = [];
            switch ($order) {
                case 'desc':
                    arsort($arr);
                    break;
                
                case 'asc':
                    asort($arr);
                    break;
            }
            
            $result = array_slice($arr, 0, $count);
            return $result;
        }

        /**
         * Calculates the percentage of each key in the array
         * @param  array  $arr    Array containing "key" => $count
         * @param  integer $places To how many places the percentage should be round off
         * @return array          Array containing "key" => percentage
         */
        public static function percentage($arr, $places = 2) {
            $arr = self::topValues($arr, count($arr));
            $total = array_sum($arr);
            $result = [];

            if ($total == 0) {
                return $result;
            }

            foreach ($arr as $key => $value) {
                $result[$key] = number_format(($value / $total) * 100, $places);
            }
            return $result;
        }

        /**
         * Function checks that all the values of $current array
         * are present in $search array
         * @param  array $search  Search Array
         * @param  array $current Array to be tested against search array
         * @param boolean $allElements All Elements in $current array should be present in $search array or not
         * @return boolean
         */
        public static function inArray($search, $current, $allElements = true) {
            $pass = true;

            $current = array_unique($current);
            foreach ($current as $c) {
                if (!in_array($c, $search)) {
                    $pass = false;
                    break;
                }
            }

            // if size of $current is more than how can all of its elements be present in $search
            if (count($current) == 0 || ($allElements && count($current) > count($search))) {
                $pass = false;
            }
            return $pass;
        }

        public static function arrayKeys($arr = [], $key = null) {
            $ans = [];
            foreach ($arr as $k => $value) {
                if ($key) {
                    $ans[] = $value->$key;
                } else {
                    $ans[] = $k;
                }
            }
            return $ans;
        }

        public static function assignDefault($arr, $indexes, $default = []) {
            foreach ($indexes as $i) {
                if (!isset($arr[$i])) {
                    $arr[$i] = $default;   
                }
            }
            return $arr;
        }

        /**
         * Unset multiple keys from an array
         * @param  array &$arr
         * @param  array $keys Mulitple keys to be unset
         */
        public static function unset(&$arr, $keys = []) {
            foreach ($keys as $k) {
                unset($arr[$k]);
            }
            return $arr;
        }

        /**
         * Remove multiple values from an array
         * @param  array $arr    Master array containing all the values
         * @param  array  $values Values to be removed if present in array
         * @return array         Final Changed Array
         */
        public static function removeValues($arr, $values = []) {
            foreach ($values as $v) {
                $index = array_search($v, $arr);

                if ($index !== false) {
                    unset($arr[$index]);   
                }
            }
            return $arr;
        }

        public static function modifyMap($arr, $mapKey = "") {
        	$result = [];
        	if (strlen($mapKey) === 0) {
        		return $result;
        	}
        	foreach ($arr as $key => $value) {
        		try {
        			if (!is_object($value)) {
        				throw new \Exception("Invalid Arguments provided");
        			}
        			$k = $value->$mapKey;
        		} catch (\Exception $e) {
        			$k = "";
        		}
        		$result[$k] = $value;
        	}
        	return $value;
        }

        public static function random($arr, $num = 1) {
        	shuffle($arr);
        	
        	$r = array();
        	for ($i = 0; $i < $num; $i++) {
        	    $r[] = $arr[$i];
        	}
        	return $num == 1 ? $r[0] : $r;
        }

        public static function customImplode($input, $glue = ",") {
        	$output = implode($glue, array_map(
        	    function ($v, $k) { return sprintf("%s=%s", $k, $v); },
        	    $input,
        	    array_keys($input)
        	));
        	return $output;
        }

        public static function arraySig($arr = []) {
        	$signature = [];
        	foreach ($arr as $key => $value) {
        		if (is_array($value)) {
        			$signature[$key] = self::arraySig($value);
        		} else {
        			$signature[$key] = sprintf("%s", $value);
        		}
        	}
        	$k = self::customImplode($signature);
        	return $k;
        }
    }
}
