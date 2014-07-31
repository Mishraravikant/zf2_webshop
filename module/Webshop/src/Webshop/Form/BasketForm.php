<?php

namespace Webshop\Form;

use Zend\Form\Form;

class BasketForm extends Form {

    public function __construct($name = null) {


        parent::__construct('Product');
        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'qty',
            'attributes' => array(
                'type' => 'text',
                'id' => 'qty',
                'required' => 'required'
            ),
            'options' => array(
                'label' => 'Quantity',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Basket'
            ),
        ));

        $this->add(array(
            'name' => 'store_product_id',
            'attributes' => array(
                'type' => 'hidden',
            // 'value' => $productID
            ),
        ));
    }

}
