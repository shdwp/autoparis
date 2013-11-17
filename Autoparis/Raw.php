<?php
namespace Autoparis;

class Raw extends Field {
    public function __construct($name, $type, $params=array()) {
        $this->type = $type;
        parent::__construct($name, $params);
    }
}
