<?php

/**
 * Description of query
 *
 * @author Faizan Ayubi
 */

namespace Framework\Database {

    use Framework\Base as Base;
    use Framework\ArrayMethods as ArrayMethods;
    use Framework\Database\Exception as Exception;

    class Query extends Base {

        /**
         * @readwrite
         */
        protected $_connector;

        /**
         * @read
         */
        protected $_from;

        /**
         * @read
         */
        protected $_fields;

        /**
         * @read
         */
        protected $_limit;

        /**
         * @read
         */
        protected $_offset;

        /**
         * @read
         */
        protected $_order;

        /**
         * @read
         */
        protected $_direction;

        /**
         * @read
         */
        protected $_join = array();

        /**
         * @read
         */
        protected $_where = array();

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        /**
         * Used to wrap the $value passed to it in the applicable quotation marks,
         * so that it can be added to the applicable query in a syntactically correct form.
         * It has logic to deal with different value types, such as strings, arrays, and boolean values.
         * @param type $value
         * @return string
         */
        protected function _quote($value) {
            if (is_string($value)) {
                $escaped = $this->connector->escape($value);
                return "'{$escaped}'";
            }
            if (is_array($value)) {
                $buffer = array();
                foreach ($value as $i) {
                    array_push($buffer, $this->_quote($i));
                }
                $buffer = join(", ", $buffer);
                return "({$buffer})";
            }
            if (is_null($value)) {
                return "NULL";
            }
            if (is_bool($value)) {
                return (int) $value;
            }
            return $this->connector->escape($value);
        }

        /**
         * Used to specify the table from which data should be read from, or written to
         * @param type $from
         * @param type $fields
         * @return \Framework\Database\Query
         * @throws Exception\Argument
         */
        public function from($from, $fields = array("*")) {
            if (empty($from)) {
                throw new Exception\Argument("Invalid argument");
            }
            $this->_from = $from;
            if ($fields) {
                $this->_fields[$from] = $fields;
            }
            return $this;
        }

        /**
         * Used to specify joins across tables. It only allows a natural JOIN, as this should do for most cases.
         * @param type $join
         * @param type $on
         * @param type $fields
         * @return \Framework\Database\Query
         * @throws Exception\Argument
         */
        public function join($join, $on, $fields = array()) {
            if (empty($join)) {
                throw new Exception\Argument("Invalid argument");
            }
            if (empty($on)) {
                throw new Exception\Argument("Invalid argument");
            }
            $this->_fields += array($join => $fields);
            $this->_join[] = "JOIN {$join} ON {$on}";
            return $this;
        }

        /**
         * Useful for specifying how many rows to return at once, and on which page to begin the results.
         * This is slightly more convenient than having to provide an offset directly.
         * The page parameter is optional.
         * @param type $limit
         * @param type $page
         * @return \Framework\Database\Query
         * @throws Exception\Argument
         */
        public function limit($limit, $page = 1) {
            if (empty($limit)) {
                throw new Exception\Argument("Invalid argument");
            }
            $this->_limit = $limit;
            $this->_offset = $limit * ($page - 1);
            return $this;
        }

        /**
         * Useful for specifying which field to order the query by, and in which direction.
         * It only handles ordering by one field, another instance of accommodating the majority of cases,
         * and defaults the direction to asc if the second (optional) $direction parameter is not specified.
         * @param type $order
         * @param type $direction
         * @return \Framework\Database\Query
         * @throws Exception\Argument
         */
        public function order($order, $direction = "asc") {
            if (empty($order)) {
                throw new Exception\Argument("Invalid argument");
            }
            $this->_order = $order;
            $this->_direction = $direction;
            return $this;
        }

        /**
         * Quoting the values passed in the second, third, fourth (etc.) positions with that _quote() method
         * so that our database data is secure from injection attacks.
         * @return \Framework\Database\Query
         * @throws Exception\Argument
         */
        public function where() {
            $arguments = func_get_args();
            if (sizeof($arguments) < 1) {
                throw new Exception\Argument("Invalid argument");
            }
            $arguments[0] = preg_replace("#\?#", "%s", $arguments[0]);
            foreach (array_slice($arguments, 1, null, true) as $i => $parameter) {
                $arguments[$i] = $this->_quote($arguments[$i]);
            }
            $this->_where[] = call_user_func_array("sprintf", $arguments);
            return $this;
        }

        /**
         * Allows the $fields arguments to be simple field names, or field names with aliases.
         * @return type
         */
        protected function _buildSelect() {
            $fields = array();
            $where = $order = $limit = $join = "";
            $template = "SELECT %s FROM %s %s %s %s %s";
            foreach ($this->fields as $table => $_fields) {
                foreach ($_fields as $field => $alias) {
                    if (is_string($field)) {
                        $fields[] = "{$field} AS {$alias}";
                    } else {
                        $fields[] = $alias;
                    }
                }
            }
            $fields = join(", ", $fields);
            $_join = $this->join;
            if (!empty($_join)) {
                $join = join(" ", $_join);
            }
            $_where = $this->where;
            if (!empty($_where)) {
                $joined = join(" AND ", $_where);
                $where = "WHERE {$joined}";
            }
            $_order = $this->order;
            if (!empty($_order)) {
                $_direction = $this->direction;
                $order = "ORDER BY {$_order} {$_direction}";
            }
            $_limit = $this->limit;
            if (!empty($_limit)) {
                $_offset = $this->offset;
                if ($_offset) {
                    $limit = "LIMIT {$_limit}, {$_offset}";
                } else {
                    $limit = "LIMIT {$_limit}";
                }
            }
            return sprintf($template, $fields, $this->from, $join, $where, $order, $limit);
        }

        /**
         * Defines the template for a valid INSERT query, and then goes about splitting the passed $data into two arrays:
         * for $fields and $values. It joins those arrays, and finally runs the results through sprintf.
         * @param type $data
         * @return type
         */
        protected function _buildInsert($data) {
            $fields = array();
            $values = array();
            $template = "INSERT INTO '%s' ('%s') VALUES (%s)";
            foreach ($data as $field => $value) {
                $fields[] = $field;
                $values[] = $this->_quote($value);
            }
            $fields = join("', '", $fields);
            $values = join(", ", $values);
            return sprintf($template, $this->from, $fields, $values);
        }

        /**
         * Defines the template for a valid UPDATE query, combines both the fields and values into a single array. 
         * @param type $data
         * @return type
         */
        protected function _buildUpdate($data) {
            $parts = array();
            $where = $limit = "";
            $template = "UPDATE %s SET %s %s %s";
            foreach ($data as $field => $value) {
                $parts[] = "{$field} = " . $this->_quote($value);
            }
            $parts = join(", ", $parts);
            $_where = $this->where;
            if (!empty($_where)) {
                $joined = join(", ", $_where);
                $where = "WHERE {$joined}";
            }
            $_limit = $this->limit;
            if (!empty($_limit)) {
                $_offset = $this->offset;
                $limit = "LIMIT {$_limit} {$_offset}";
            }
            return sprintf($template, $this->from, $parts, $where, $limit);
        }

        /**
         * Does everything the _buildUpdate() method does, and nothing that the _buildInsert() method does.
         * This is because there is no row data to take into account except for whatever the WHERE clauses target.
         * @return type
         */
        protected function _buildDelete() {
            $where = $limit = "";
            $template = "DELETE FROM %s %s %s";
            $_where = $this->where;
            if (!empty($_where)) {
                $joined = join(", ", $_where);
                $where = "WHERE {$joined}";
            }
            $_limit = $this->limit;
            if (!empty($_limit)) {
                $_offset = $this->offset;
                $limit = "LIMIT {$_limit} {$_offset}";
            }
            return sprintf($template, $this->from, $where, $limit);
        }

        /**
         * Determines what kind of query you need by looking at whether you have called the where() method on this query object.
         * If they do execute correctly, and an INSERT query was performed, the last inserted ID will be returned.
         * If an UPDATE query was performed, 0 is returned (as a means of specifying that the operation was successful).
         * @param type $data
         * @return int
         * @throws Exception\Sql
         */
        public function save($data) {
            $isInsert = sizeof($this->_where) == 0;
            if ($isInsert) {
                $sql = $this->_buildInsert($data);
            } else {
                $sql = $this->_buildUpdate($data);
            }
            $result = $this->_connector->execute($sql);
            if ($result === false) {
                throw new Exception\Sql();
            }
            if ($isInsert) {
                return $this->_connector->lastInsertId;
            } return 0;
        }

        /**
         * Simply calls the _buildDelete() method and executes its result.
         * @return type
         * @throws Exception\Sql
         */
        public function delete() {
            $sql = $this->_buildDelete();
            $result = $this->_connector->execute($sql);
            if ($result === false) {
                throw new Exception\Sql();
            }
            return $this->_connector->affectedRows;
        }

        /**
         * Alters the  $_limit and $_offset properties so that only one row is returned.
         * @return type
         */
        public function first() {
            $limit = $this->_limit;
            $offset = $this->_offset;
            $this->limit(1);
            $all = $this->all();
            $first = ArrayMethods::first($all);
            if ($limit) {
                $this->_limit = $limit;
            } if ($offset) {
                $this->_offset = $offset;
            }
            return $first;
        }

        /**
         * Alters the $_limit and $_offset properties, and also the $_fields property.
         * @return type
         */
        public function count() {
            $limit = $this->limit;
            $offset = $this->offset;
            $fields = $this->fields;
            $this->_fields = array($this->from =>array("COUNT(1)" =>"rows"));
            $this->limit(1);
            $row = $this->first();
            $this->_fields = $fields;
            if ($fields) {
                $this->_fields = $fields;
            } if ($limit) {
                $this->_limit = $limit;
            } if ($offset) {
                $this->_offset = $offset;
            }
            return $row["rows"];
        }

    }

}