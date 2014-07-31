<?php

namespace Webshop\Form;

use Zend\InputFilter\InputFilter;

class ProductFilter extends InputFilter {

    public function __construct() {
        $this->add(array(
            'name' => 'name',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 140,
                    ),
                ),
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'required' => true,
            'filters' => array(
                array(
                    'name' => 'StripTags',
                ),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 2,
                        'max' => 500,
                    ),
                ),
            ),
        ));
        
                $this->add(array(
            'name' => 'price',
            'required' => true,

        ));


    }

}