<?php

namespace App\Models;

class Data
{
    public function __construct($data)
    {
        $this->data= $data;
    }

    public function toArray()
    {
        return (array) $this->data;
    }
}
