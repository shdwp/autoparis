<?php
namespace Autoparis;

abstract class AutoModel extends \Model {
    protected $instances = [];

    public abstract function getFields();

    public function getField($key) {
        foreach ($this->getFields() as $field) {
            if ($field->getName() == $key) {
                return $field;
            }
        }
        return null;
    }

    public function __get($prop) {
        return call_user_func_array(
            array($this, "get"),
            func_get_args()
        );
    }

    public function __set($prop, $value) {
        return call_user_func_array(
            array($this, "set"),
            func_get_args()
        );
    }

    public function set($property, $value) {
        if (array_key_exists($property, $this->instances))
            unset($this->instances[$property]);

        return call_user_func_array(
            array("parent", "set"),
            func_get_args()
        );
    }

    public function get($property) {
        $field = $this->getField($property);
        if ($field instanceof DateTime) {
            return \DateTime::createFromFormat("Y-m-d H:i:s", parent::get($property));
        } else {
            return call_user_func_array(
                "parent::get",
                func_get_args()
            );
        } 
    }

    public function foreign_key($key, $field = null) {
        if (array_key_exists($key, $this->instances)) {
            return $this->instances[$key];
        } else {
            $this->instances[$key] = $this->belongs_to($key, $field)->find_one();
            return $this->foreign_key($key, $field);
        }
    }

    public function save() {
        foreach ($this->getFields() as $field) {
            $field->populate($this);
        }

        return call_user_func_array('parent::save', func_get_args());
    }
}
