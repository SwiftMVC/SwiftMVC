<?php

namespace Framework {

    use Framework\Base as Base;
    use Framework\Cache as Cache;
    use Framework\Cache\Exception as Exception;

    class Cache extends Base {

        /**
         * @readwrite
         */
        protected $_type;

        /**
         * @readwrite
         */
        protected $_options;

        public function connect() {
            try {
                $this->_service = new \Memcache();
                $this->_service->connect($this->host, $this->port);
                $this->isConnected = true;
            } catch (\Exception $e) {
                throw new Exception\Service("Unable to connect to service");
            }
            return $this;
        }

        public function disconnect() {
            if ($this->_isValidService()) {
                $this->_service->close();
                $this->isConnected = false;
            }
            return $this;
        }

        public function get($key, $default = null) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            $value = $this->_service->get($key, MEMCACHE_COMPRESSED);
            if ($value) {
                return $value;
            } return $default;
        }

        public function set($key, $value, $duration = 120) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            $this->_service->set($key, $value, MEMCACHE_COMPRESSED, $duration);
            return $this;
        }

        public function erase($key) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            $this->_service->delete($key);
            return $this;
        }

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        public function initialize() {
            $type = $this->getType();
            if (empty($type)) {
                $configuration = Registry::get("configuration");
                if ($configuration) {
                    $configuration = $configuration->initialize();
                    $parsed = $configuration->parse("configuration/cache");
                    if (!empty($parsed->cache->default) &&!empty($parsed->cache->default->type)) {
                        $type = $parsed->cache->default->type;
                        unset($parsed->cache->default->type);
                        $this->__construct(array(
                            "type" => $type,
                            "options" => (array) $parsed->cache->default 
                        ));
                    }
                }
            }
            if (empty($type)) {
                throw new Exception\Argument("Invalid type");
            }
            switch ($type) {
                case "memcached": {
                        return new Cache\Driver\Memcached($this->getOptions());
                        break;
                    }
                default: {
                        throw new Exception\Argument("Invalid type");
                        break;
                    }
            }
        }

    }

}