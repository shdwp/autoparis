<?php
namespace AutoParis;

class Id extends Field {
    protected $type = "INTEGER PRIMARY KEY AUTO_INCREMENT";

    public function __construct($name='id', $params=[]) {
        parent::__construct($name, $params);
    }
}
