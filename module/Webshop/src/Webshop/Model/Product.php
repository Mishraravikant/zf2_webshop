<?php

namespace Webshop\Model;

class Product {

    public $id;
    public $filename;
    public $name;
    public $description;
    public $price;

    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;

        $this->filename = (isset($data['filename'])) ?
                $data['filename'] : null;

        $this->name = (isset($data['name'])) ?
                $data['name'] : null;
        $this->description = (isset($data['description'])) ?
                $data['description'] : null;
        $this->price = (isset($data['price'])) ?
                $data['price'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

    public function getFilename() {
        return $this->filename;
    }

}