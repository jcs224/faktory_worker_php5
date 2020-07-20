<?php

namespace FaktoryQueue;

class FaktoryJob implements \JsonSerializable {
    private $id;
    private $type;
    private $args;
    private $at;

    public function __construct($type, $args) {
        $this->id = uniqid();
        $this->type = $type;
        $this->args = $args;
        $this->at = date(DATE_RFC3339);
    }

    public function jsonSerialize() {
        return [
            'jid' => $this->id,
            'jobtype' => $this->type,
            'args' => $this->args,
            'at' => $this->at,
        ];
    }

    public function inSeconds($seconds) {
        if (!is_numeric($seconds)) {
            throw new \Exception('expected a number in seconds');
        }
        $date = date(DATE_RFC3339, time() + $seconds);
        $this->at($date);
    }

    public function inMinutes($minutes) {
        if (!is_numeric($minutes)) {
            throw new \Exception('expected a number in minutes');
        }
        $seconds = $minutes * 60;
        $date = date(DATE_RFC3339, time() + $seconds);
        $this->at($date);
    }

    public function inHours($hours) {
        if (!is_numeric($hours)) {
            throw new \Exception('expected a number in hours');
        }
        $seconds = $hours * 3600;
        $date = date(DATE_RFC3339, time() + $seconds);
        $this->at($date);
    }

    public function at($date) {
        if (!$this->validateDate($date)) {
            throw new \Exception('expected a date in RFC3339 format');
        }

        $this->at = $date;
    }

    private function validateDate($date, $format = DATE_RFC3339) {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
