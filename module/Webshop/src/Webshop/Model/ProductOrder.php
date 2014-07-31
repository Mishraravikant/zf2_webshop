<?php

namespace Webshop\Model;

class ProductOrder {

    public $id;
    public $store_product_id;
    public $qty;
    public $total;
    public $status;
    public $stamp;
    public $user_id;
    public $name;
    //  public $last_name;
    public $email;
    public $filename;
    public $product_name;
    public $price;
    protected $_product;

    public function __construct(Product $product = NULL) {
        $this->status = 'new';

        if (!empty($product)) {
            $this->setProduct($product);
        }
    }

    function exchangeArray($data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->store_product_id = (isset($data['store_product_id'])) ? $data['store_product_id'] : null;
        $this->qty = (isset($data['qty'])) ? $data['qty'] : null;
        $this->total = (isset($data['total'])) ? $data['total'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
        $this->stamp = (isset($data['stamp'])) ? $data['stamp'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        //$this->last_name		= (isset($data['last_name'])) ? $data['last_name'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        //$this->ship_to_street		= (isset($data['ship_to_street'])) ? $data['ship_to_street'] : null;
        //$this->ship_to_city		= (isset($data['ship_to_city'])) ? $data['ship_to_city'] : null;
        //$this->ship_to_state		= (isset($data['ship_to_state'])) ? $data['ship_to_state'] : null;
        //$this->ship_to_zip		= (isset($data['ship_to_zip'])) ? $data['ship_to_zip'] : null;

        $this->filename = (isset($data['filename'])) ? $data['filename'] : null;
        $this->product_name = (isset($data['product_name'])) ? $data['product_name'] : null;
        $this->price = (isset($data['price'])) ? $data['price'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }

    public function setProduct(Product $product) {
        $this->_product = $product;
        $this->store_product_id = $product->id;
    }

    public function getProduct() {
        return $this->_product;
    }

    public function calculateSubTotal() {
        if (null === $this->_product) {
            return 0;
        } else {
            $this->total = $this->qty * $this->_product->price;
            return $this->total;
        }
    }

    public function setQuantity($quantity) {
        $this->qty = $quantity;
        if (!empty($this->_product)) {
            $this->calculateSubTotal();
        }
    }

    public function setShopperData($data) {
        $this->name = $data["name"];
        $this->email = $data["email"];
        $this->user_id = $data["user_id"];
    }

}
