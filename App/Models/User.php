<?php

namespace App\Models;

class User
{

    public $errors = [];

    public function __construct($data = [])
    {

        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
}
