<?php
namespace Autoparis;

class DateTime extends Field {
    protected $type = "DATETIME";

    public function populate($instance) {
        if ($this->param("default") === "now") {
            if ($instance->get($this->getName()) == null) {
                $instance->set_expr($this->getName(), "NOW()");
            }
        } else if($this->param("auto") === "now") {
            $instance->set_expr($this->getName(), "NOW()");
        }
    }
}
