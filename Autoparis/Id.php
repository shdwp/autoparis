<?php
namespace Autoparis;

class Id extends Field {
    protected $type = "INTEGER PRIMARY KEY AUTO_INCREMENT";

    public function __construct($name='id', $params=array()) {
        parent::__construct($name, $params);
    }
}
