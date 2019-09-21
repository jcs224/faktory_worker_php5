<?php

namespace FaktoryQueue;

class FaktoryJob implements \JsonSerializable {
    private $id;
    private $type;
    private $args;

    public function __construct($type, $args) {
        $this->id = uniqid();
        $this->type = $type;
        $this->args = $args;
    }

    public function jsonSerialize() {
        return [
            'jid' => $this->id,
            'jobtype' => $this->type,
            'args' => $this->args
        ];
    }
}