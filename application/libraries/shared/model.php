<?php

/**
 * Contains similar code of all models and some helpful methods
 *
 * @author Faizan Ayubi
 */

namespace Shared {

    class Model extends \Framework\Model {

        /**
         * @column
         * @readwrite
         * @primary
         * @type autonumber
         */
        protected $_id;

        /**
         * @column
         * @readwrite
         * @type boolean
         * @index
         */
        protected $_live;

        /**
         * @column
         * @readwrite
         * @type datetime
         */
        protected $_created;

        /**
         * @column
         * @readwrite
         * @type datetime
         */
        protected $_modified;

        /**
         * Every time a row is created these fields should be populated with default values.
         */
        public function save() {
            $primary = $this->getPrimaryColumn();
            $raw = $primary["raw"];
            if (empty($this-> $raw)) {
                $this->setCreated(date("Y-m-d H:i:s"));
                $this->setLive(true);
            }
            $this->setModified(date("Y-m-d H:i:s"));
            parent::save();
        }

        /**
         * Renders the form fields for different properties of a model
         * with validations and type which can be looped through in the views
         */
        public function render() {
            $fields = array();
            foreach ($this->columns as $column) {
                if (!$column["label"]) {
                    continue;
                }

                $r = $this->particularFields($column["name"]);
                $arr = array(
                    "name" => $column["name"],
                    "placeholder" => $column["label"],
                    "type" => $r["type"]
                );
                if ($column["validate"]) {
                    $v = $this->parseValidations($column["validate"]);
                    $arr = array_merge($arr, $v);
                }
                $fields[$arr['name']] = $arr;
            }
            return $fields;
        }

        private function particularFields($field) {
            switch ($field) {
                case 'name':
                    $type = 'text';
                    break;
                
                case 'password':
                    $type = 'password';
                    break;

                case 'email':
                    $type = 'email';
                    break;

                case 'phone':
                    $type = "text";
                    break;

                default:
                    $type = 'text';
                    break;
            }
            return array("type" => $type);
        }

        private function parseValidations($validations) {
            $html = ''; $pattern = '';
            foreach ($validations as $key => $value) {
                preg_match("/(\w+)(\((\d+)\))?/", $value, $matches);
                $type = isset($matches[1]) ? $matches[1] : 'none';
                switch ($type) {
                    case 'required':
                        $html .= ' required="" ';
                        break;
                    
                    case 'max':
                        $html .= ' maxlength="' .$matches[3] . '" ';
                        break;

                    case 'min':
                        $pattern .= ' pattern="(.){' . $matches[3] . ',}" ';
                        break;
                }
            }
            return array("html" => $html, "pattern" => $pattern);
        }
    }
}
