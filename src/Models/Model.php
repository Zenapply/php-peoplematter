<?php

namespace Zenapply\HRIS\PeopleMatter\Models;

class Model
{
    protected $properties = [
        
    ];
    
    public function __construct($options)
    {
        foreach ($this->properties as $key) {
            $this->{$key} = "";
        }

        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    public function toArray()
    {
        $arr = [];
        foreach ($this->properties as $key) {
            $arr[$key] = $this->{$key};
        }
        return $arr;
    }
}
