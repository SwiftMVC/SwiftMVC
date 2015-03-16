<?php

namespace Framework\Cache\Driver {

    use Framework\Cache as Cache;
    use Framework\Cache\Exception as Exception;

    class Memcached extends Cache\Driver {

        protected $_service;

        /**
         * @readwrite
         */
        protected $_host = "127.0.0.1";

        /**
         * @readwrite
         */
        protected $_port = "11211";
        
        /**
         * @readwrite
         */
        protected $_isConnected = false;

        protected function _isValidService() {
            $isEmpty = empty($this->_service);
            $isInstance = $this->_service instanceof \Memcache;
            if ($this->isConnected && $isInstance &&!$isEmpty) {
                return true;
            }
            return false;
        }

    }

}