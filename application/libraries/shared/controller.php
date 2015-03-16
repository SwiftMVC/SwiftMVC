<?php

/**
 * Subclass the Controller class within our application.
 *
 * @author Faizan Ayubi
 */

namespace Shared {

    class Controller extends \Framework\Controller {

        public function __construct($options = array()) {
            parent::__construct($options);
            $database = \Framework\Registry::get("database");
            $database->connect();
        }

    }

}
