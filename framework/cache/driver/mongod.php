<?php

namespace Framework\Cache\Driver {

    use Framework\Cache as Cache;
    use Framework\Cache\Exception as Exception;

    /**
     * Makes use of the inherited accessor support. It defaults the $_host and $_port properties to common values, and calls the parent::__construct($options) method.
     * Connections to the Mongod server are made via the connect() public method from within the __construct() method.
     * 
     * The driver also has a protected _isValidService() method that is used ensure that the value of the $_service is a valid Mongod instance. Let us look at the connect()/disconnect() methods
     */
    class Mongod extends Cache\Driver {

        /**
         * Instance of PHP’s Mongod class
         * @var type 
         */
        protected $_service;

        /**
         * @readwrite
         */
        protected $_host = "127.0.0.1";

        /**
         * @readwrite
         */
        protected $_port = "27017";

        /**
         * @readwrite
         */
        protected $_isConnected = false;

        /**
         * @readwrite
         */
        protected $_db;

        protected function _isValidService() {
            $isEmpty = empty($this->_service);
            $isInstance = $this->_service instanceof \Mongo;
            if ($this->isConnected && $isInstance && !$isEmpty) {
                return true;
            }
            return false;
        }

        /**
         * Attempts to connect to the MongoDB server at the specified host/port. If it connects, 
         * @return \Framework\Cache\Driver\Mongod
         * @throws Exception\Service
         */
        public function connect($db) {
            try {
                $this->_service = new \Mongo();
                $this->isConnected = true;
                $this->db = $this->_service->selectDB($db);
            } catch (\Exception $e) {
                throw new Exception\Service("Unable to connect to service");
            }

            return $this;
        }

        /**
         * Attempts to disconnect the $_service instance from the Mongod service. It will only do so if the _isValidService() method returns true.
         * @return \Framework\Cache\Driver\Mongod
         */
        public function disconnect() {
            if ($this->_isValidService()) {
                $this->_service->close();
                $this->isConnected = false;
            }

            return $this;
        }

        public function getById($collection, $id) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            // Convert strings of right length to MongoID
            if (strlen($id) == 24) {
                $id = new \MongoId($id);
            }
            $table = $this->db->selectCollection($collection);
            $cursor  = $table->find(array('_id' => $id));
            $article = $cursor->getNext();
            if (!$article ) {
                return false;
            }
            return $article;
        }

        /**
         * Get Cursor of data queried through options
         * @param  string $collection the name of table in mongodb
         * @param  array  $options    query
         * @return array  the cursor to data
         * @throws Exception\Service
         */
        public function get($collection, $options = array(), $limit = NULL, $order = NULL, $direction = -1, $page = 1) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }

            $table = $this->db->selectCollection($collection);
            $cursor = $table->find($options);

            if ($limit) {
                $cursor->limit($limit);
            }
            if ($order) {
                $cursor->sort(array("{$order} => {$direction}"));
            }
            if ($limit && $page) {
                $cursor->skip($limit * ($page - 1));
            }

            return $cursor;
        }

        public function create($collection, $doc) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            $table = $this->db->selectCollection($collection);
            return $table->insert($doc);
        }

        /**
         * Updates the row
         * @return \Framework\Cache\Driver\Mongod
         * @throws Exception\Service
         */
        public function update($collection, $id, $options) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            $table = $this->db->selectCollection($collection);
            $table->update($options);
            return $this;
        }

        public function delete($collection, $id) {
            if (!$this->_isValidService()) {
                throw new Exception\Service("Not connected to a valid service");
            }
            if (strlen($id) == 24){
                $id = new \MongoId($id);
            }
            $table = $this->db->selectCollection($collection);
            $result = $table->remove(array('_id'=>$id));
            if (!$id){
                return false;
            }
            return $result;
        }
    }

}