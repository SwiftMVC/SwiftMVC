<?php

/**
 * A simple class that will help to organize the tests and evaluate the results
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    class Test {

        private static $_tests = array();

        /**
         * Adds test for execution
         * @param type $callback
         * @param type $title
         * @param type $set
         */
        public static function add($callback, $title = "Unnamed Test", $set = "General") {
            self::$_tests[] = array(
                "set" => $set,
                "title" => $title,
                "callback" => $callback
            );
        }

        /**
         * Loops through the tests and executes them. If the test passes, it is added to an array of passed tests.
         * If it fails, it is added to an array of failed tests.
         * If, during any test, an exception occurs, the test title/ set/exception type will be added to an array of exceptions.
         * 
         * @param type $before
         * @param type $after
         * @return type
         */
        public static function run($before = null, $after = null) {
            if ($before) {
                $before($this->_tests);
            }
            $passed = array();
            $failed = array();
            $exceptions = array();
            foreach (self::$_tests as $test) {
                try {
                    $result = call_user_func($test["callback"]);
                    if ($result) {
                        $passed[] = array("set" => $test["set"], "title" => $test["title"]);
                    } else {
                        $failed[] = array("set" => $test["set"], "title" => $test["title"]);
                    }
                } catch (\Exception $e) {
                    $exceptions[] = array("set" => $test["set"], "title" => $test["title"], "type" => get_class($e)
                    );
                }
            }
            if ($after) {
                $after($this->_tests);
            }
            return array(
                "passed" => $passed,
                "failed" => $failed,
                "exceptions" => $exceptions
            );
        }

    }

}