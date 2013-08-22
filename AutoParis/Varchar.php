<?php
namespace AutoParis;

class Varchar extends Field {
    protected $type = "VARCHAR(%d)";

    public function __construct($name, $length=128, $params=[]) {
        $this->length = $length;
        $this->type = sprintf($this->type, $this->length);
        parent::__construct($name, $params);
    }
}
