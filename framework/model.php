<?php

/**
 * Model class begins simply enough, with the properties we want getters/setters for, 
 * and the exception- generation override methods.
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    use Framework\Base as Base;
    use Framework\Registry as Registry;
    use Framework\Inspector as Inspector;
    use Framework\StringMethods as StringMethods;
    use Framework\Model\Exception as Exception;

    class Model extends Base {

        /**
         * @readwrite
         */
        protected $_table;

        /**
         * @readwrite
         */
        protected $_connector;

        /**
         * @read
         */
        protected $_types = array("autonumber", "text", "integer", "decimal", "boolean", "datetime");
        protected $_columns;
        protected $_primary;

        public function __construct($options = array()) {
            parent::__construct($options);
            $this->load();
        }

        /**
         * Determines the model’s primary column and checks to see whether it is not empty.
         * This tells us whether the primary key has been provided,
         * which gives us a viable means of ﬁnding the intended record. If the primary key class property is empty,
         * we assume this model instance is intended for the creation of a new record, and do nothing further.
         * 
         * @throws Exception\Primary
         */
        public function load() {
            $primary = $this->primaryColumn;
            $raw = $primary["raw"];
            $name = $primary["name"];
            if (!empty($this->$raw)) {
                $previous = $this->connector->query()->from($this->table)->where("{$name} = ?", $this->$raw)->ﬁrst();
                if ($previous == null) {
                    throw new Exception\Primary("Primary key value invalid");
                } foreach ($previous as $key => $value) {
                    $prop = "_{$key}";
                    if (!empty($previous->$key) && !isset($this->$prop)) {
                        $this->$key = $previous->$key;
                    }
                }
            }
        }

        /**
         * Creates a query instance, and targets the table related to the Model class. 
         * @return type
         */
        public function save() {
            $primary = $this->primaryColumn;
            $raw = $primary["raw"];
            $name = $primary["name"];
            $query = $this->connector->query()->from($this->table);
            if (!empty($this->$raw)) {
                $query->where("{$name} = ?", $this->$raw);
            } $data = array();
            foreach ($this->columns as $key => $column) {
                if (!$column["read"]) {
                    $prop = $column["raw"];
                    $data[$key] = $this->$prop;
                    continue;
                } if ($column != $this->primaryColumn && $column) {
                    $method = "get" . ucﬁrst($key);
                    $data[$key] = $this->$method();
                    continue;
                }
            } $result = $query->save($data);
            if ($result > 0) {
                $this->$raw = $result;
            } return $result;
        }

        /**
         * Creates a query object, only if the primary key property value is not empty
         * @return type
         */
        public function delete() {
            $primary = $this->primaryColumn;
            $raw = $primary["raw"];
            $name = $primary["name"];
            if (!empty($this->$raw)) {
                return $this->connector->query()->from($this->table)->where("{$name} = ?", $this->$raw)->delete();
            }
        }

        /**
         * Creates a query object, only if the primary key property value is not empty but statistacally
         * @param type $where
         * @return type
         */
        public static function deleteAll($where = array()) {
            $instance = new static();
            $query = $instance->connector->query()->from($instance->table);
            foreach ($where as $clause => $value) {
                $query->where($clause, $value);
            }
            return $query->delete();
        }

        /**
         * Simple, static wrapper method for the protected _all() method
         * @param type $where
         * @param type $ﬁelds
         * @param type $order
         * @param type $direction
         * @param type $limit
         * @param type $page
         * @return type
         */
        public static function all($where = array(), $ﬁelds = array("*"), $order = null, $direction = null, $limit = null, $page = null) {
            $model = new static();
            return $model->_all($where, $ﬁelds, $order, $direction, $limit, $page);
        }

        /**
         * Creates a query, taking into account the various ﬁlters and ﬂags, to return all matching records.
         * @param type $where
         * @param type $ﬁelds
         * @param type $order
         * @param type $direction
         * @param type $limit
         * @param type $page
         * @return \Framework\class
         */
        protected function _all($where = array(), $ﬁelds = array("*"), $order = null, $direction = null, $limit = null, $page = null) {
            $query = $this->connector->query()->from($this->table, $ﬁelds);
            foreach ($where as $clause => $value) {
                $query->where($clause, $value);
            } if ($order != null) {
                $query->order($order, $direction);
            } if ($limit != null) {
                $query->limit($limit, $page);
            } $rows = array();
            $class = get_class($this);
            foreach ($query->all() as $row) {
                $rows[] = new $class($row);
            } return $rows;
        }

        /**
         * Simple, static wrapper method to a protected instance method _first
         * @param type $where
         * @param type $ﬁelds
         * @param type $order
         * @param type $direction
         * @return type
         */
        public static function ﬁrst($where = array(), $ﬁelds = array("*"), $order = null, $direction = null) {
            $model = new static();
            return $model->_ﬁrst($where, $ﬁelds, $order, $direction);
        }

        /**
         * Simply returns the ﬁrst matched record
         * @param type $where
         * @param type $ﬁelds
         * @param type $order
         * @param type $direction
         * @return \Framework\class
         */
        protected function _ﬁrst($where = array(), $ﬁelds = array("*"), $order = null, $direction = null) {
            $query = $this->connector->query()->from($this->table, $ﬁelds);
            foreach ($where as $clause => $value) {
                $query->where($clause, $value);
            } if ($order != null) {
                $query->order($order, $direction);
            } $ﬁrst = $query->ﬁrst();
            $class = get_class($this);
            if ($ﬁrst) {
                return new $class($query->ﬁrst());
            } return null;
        }

        /**
         * Simple, static wrapper method to a protected instance method _count
         * @param type $where
         * @return type
         */
        public static function count($where = array()) {
            $model = new static();
            return $model->_count($where);
        }

        /**
         * Returns a count of the matched records.
         * @param type $where
         * @return type
         */
        protected function _count($where = array()) {
            $query = $this->connector->query()->from($this->table);
            foreach ($where as $clause => $value) {
                $query->where($clause, $value);
            } return $query->count();
        }

        public function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        /**
         * Return a user-deﬁned table name or else default to the singular form of the current Model’s class name
         * @return type
         */
        public function getTable() {
            if (empty($this->_table)) {
                $this->_table = strtolower(StringMethods::singular(get_class($this)));
            } return $this->_table;
        }

        /**
         * Return the contents of the $_connector property, a connector instance stored in the Registry class,
         * or raise a Model\Exception\Connector.
         * @return type
         * @throws Exception\Connector
         */
        public function getConnector() {
            if (empty($this->_connector)) {
                $database = Registry::get("database");
                if (!$database) {
                    throw new Exception\Connector("No connector availible");
                } $this->_connector = $database->initialize();
            } return $this->_connector;
        }

        /**
         * Returns an associative array of column data.
         * @return type
         * @throws Exception\Type
         * @throws Exception\Primary
         */
        public function getColumns() {
            if (empty($_columns)) {
                $primaries = 0;
                $columns = array();
                $class = get_class($this);
                $types = $this->types;
                $inspector = new Inspector($this);
                $properties = $inspector->getClassProperties();
                $ﬁrst = function($array, $key) {
                    if (!empty($array[$key]) && sizeof($array[$key]) == 1) {
                        return $array[$key][0];
                    }
                    return null;
                };
                foreach ($properties as $property) {
                    $propertyMeta = $inspector->getPropertyMeta($property);
                    if (!empty($propertyMeta["@column"])) {
                        $name = preg_replace("#^_#", "", $property);
                        $primary = !empty($propertyMeta["@primary"]);
                        $type = $ﬁrst($propertyMeta, "@type");
                        $length = $ﬁrst($propertyMeta, "@length");
                        $index = !empty($propertyMeta["@index"]);
                        $readwrite = !empty($propertyMeta["@readwrite"]);
                        $read = !empty($propertyMeta["@read"]) || $readwrite;
                        $write = !empty($propertyMeta["@write"]) || $readwrite;
                        $validate = !empty($propertyMeta["@validate"]) ? $propertyMeta["@validate"] : false;
                        $label = $ﬁrst($propertyMeta, "@label");
                        if (!in_array($type, $types)) {
                            throw new Exception\Type("{$type} is not a valid type");
                        }
                        if ($primary) {
                            $primaries++;
                        }
                        $columns[$name] = array(
                            "raw" => $property,
                            "name" => $name,
                            "primary" => $primary,
                            "type" => $type,
                            "length" => $length,
                            "index" => $index,
                            "read" => $read,
                            "write" => $write,
                            "validate" => $validate,
                            "label" => $label
                        );
                    }
                } if ($primaries !== 1) {
                    throw new Exception\Primary("{$class} must have exactly one @primary column");
                }
                $this->_columns = $columns;
            } return $this->_columns;
        }

        /**
         * Returns a column by name.
         * @param type $name
         * @return type
         */
        public function getColumn($name) {
            if (!empty($this->_columns[$name])) {
                return $this->_columns[$name];
            } return null;
        }

        /**
         * Loops through the columns, returning the one marked as primary.
         * @return type
         */
        public function getPrimaryColumn() {
            if (!isset($this->_primary)) {
                $primary;
                foreach ($this->columns as $column) {
                    if ($column["primary"]) {
                        $primary = $column;
                        break;
                    }
                }
                $this->_primary = $primary;
            } return $this->_primary;
        }

    }

}