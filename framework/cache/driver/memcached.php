<?php

namespace Framework\Cache\Driver {

    use Framework\Cache as Cache;
    use Framework\Cache\Exception as Exception;

    /**
     * Makes use of the inherited accessor support. It defaults the $_host and $_port properties to common values, and calls the parent::__construct($options) method.
     * Connections to the Memcached server are made via the connect() public method from within the __construct() method.
     * 
     * The driver also has a protected _isValidService() method that is used ensure that the value of the $_service is a valid Memcached instance. Let us look at the connect()/disconnect() methods
     */
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