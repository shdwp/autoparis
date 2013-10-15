<?php
namespace Autoparis;

abstract class Field {
    protected $name, $params, $type;

    public function __construct($name, $params=[]) {
        $this->name = $name;
        $this->params = $params;
    }

    protected function param($key) {
        if (array_key_exists($key, $this->params))
            return $this->params[$key];
        else
            return null;
    }

    protected function paramExists($key) {
        return array_key_exists($key, $this->params);
    }

    protected function genDefault() {
        if ($this->paramExists("default")) 
            return sprintf(
                "DEFAULT \"%s\"", 
                ($this->param("default")) // @TODO: idiorm escape 
            );
    }

    protected function genNotNull() {
        if ($this->param('nn'))
            return "NOT NULL";
    }

    protected function genType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }

    public function getType($dbtype) {
        $gens = array(
            "genType",
            "genDefault",
            "genNotNull",
        );

        return $this->type = implode(
            " ", 
            array_map(
                function ($e) {
                    return call_user_func(array($this, $e));
                }, 
                $gens));
    }

    public function populate($instance) {}

}
