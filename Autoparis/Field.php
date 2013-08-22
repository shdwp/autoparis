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

    public function getName() {
        return $this->name;
    }

    public function populate($instance) {}

    public function getType($dbtype) {
        if ($this->param('nn'))
            $this->type .= " NOT NULL ";

        /*
         * @TODO
        if ($this->param("default")) 
            $this->type .= sprintf(
                " DEFAULT \"%s\" ", 
                ($this->param("default")) // @TODO: idiorm escape 
            );
         */
        return implode(" ", explode(" ", $this->type));
    }
}
