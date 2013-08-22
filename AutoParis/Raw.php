<?php
namespace AutoParis;

class Raw extends Field {
    public function __construct($name, $type, $params=[]) {
        $this->type = $type;
        parent::__construct($name, $params);
    }
}
