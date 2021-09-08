<?php

namespace App\Models;


class _Income 
{
    
    public function __construct($data = [])
    {

        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }
}