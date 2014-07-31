<?php

// filename : module/Users/src/Users/Form/RegisterForm.php

namespace Webshop\Form;

use Zend\Form\Form;

class ProductForm extends Form {

    public function __construct($name = null) {
        parent::__construct('Product');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

     $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
            'options' => array(
                'label' => 'ID',
            ),

        )); 
        
                
        $this->add(array(
            'name' => 'imageupload',
            'attributes' => array(
                'type'  => 'file',
            ),
            'options' => array(
                'label' => 'Product picture',
            ),
        )); 


        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Product name',
            ),
            'attributes' => array(
                'required' => 'required'
            ),
        ));


        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type' => 'text',
            ),
            'options' => array(
                'label' => 'Description',
            ),
            'attributes' => array(
                'required' => 'required'
            ),
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
        ));

        $this->add(array(
            'name' => 'price',
            'options' => array(
                'label' => 'Price',
            ),
        ));





        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'OK',
                'id' => 'submitbutton',
            ),
        ));
    }


    
    
}