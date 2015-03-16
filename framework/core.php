<?php

/**
 * Description of core
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    class Core {
        
        public function initialize() {
            spl_autoload_register(array($this, 'autoload'));
            spl_autoload_register(__CLASS__.'::load');
        }

        public function autoload($class) {
            $paths = explode(PATH_SEPARATOR, get_include_path());
            $ﬂags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
            $ﬁle = strtolower(str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\"))) . ".php";
            foreach ($paths as $path) {
                $combined = $path . DIRECTORY_SEPARATOR . $ﬁle;
                if (ﬁle_exists($combined)) {
                    include($combined);
                    return;
                }
            } throw new Exception("{$class} not found");
        }

    }

}
