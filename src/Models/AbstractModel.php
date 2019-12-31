<?php

namespace Main\Models;

use PDO;

abstract class AbstractModel {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }
}