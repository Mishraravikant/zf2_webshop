<?php

namespace Webshop\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Webshop\Form\ProductForm;
use Webshop\Form\ProductFilter;
use Webshop\Model\Product;
use Webshop\Model\ProductTable;
use Zend\Session\Container; // We need this when using sessions

class ProductController extends AbstractActionController {

    public function __construct() {
        
    }

    public function indexAction() {


        $productTable = $this->getServiceLocator()->get('ProductTable');
        $products = $productTable->fetchAll();
        //  var_dump($products);

        $viewModel = new ViewModel(array('products' => $products));
        return $viewModel;
    }

    public function viewAction() {

        $productTable = $this->getServiceLocator()->get('ProductTable');

        $productID = $this->params()->fromQuery('productid');
        //  echo "id:" . $productID;
        // var_dump($this->params()->fromRoute());

        $product = $productTable->getProduct($productID);

        //  var_dump($product);

        $form = $this->getServiceLocator()->get('BasketForm');
        $form->get('store_product_id')->setValue($productID);


        $viewModel = new ViewModel(array('product' => $product, 'form' => $form));
        return $viewModel;
    }

    public function shoppingCartAction() {
        $request = $this->getRequest();

        $productId = $request->getPost()->get('store_product_id');
        $quantity = $request->getPost()->get('qty');

        $orderTable = $this->getServiceLocator()->get('ProductOrdersTable');
        $productTable = $this->getServiceLocator()->get('ProductTable');
        $product = $productTable->getProduct($productId);

        // Store Order
        $newOrder = new \Webshop\Model\ProductOrder($product);
        $newOrder->setQuantity($quantity);


        $userArr = $this->getServiceLocator()->get('AuthService')->getStorage()->read();
        $shopperData = array(
            "name" => $userArr["name"],
            "email" => $userArr["email"],
            "user_id" => $userArr["id"]
        );
        // echo "shopperData:"; var_dump($shopperData);
        $newOrder->setShopperData($shopperData);

        $orderId = $orderTable->saveOrder($newOrder);



        return $this->redirect()->toRoute(NULL, array(
                    'controller' => 'product',
                    'action' => 'shoppingCartContent'
        ));
    }

    public function shoppingCartContentAction() {


        $orderTable = $this->getServiceLocator()->get('ProductOrdersTable');

        $userArr = $this->getServiceLocator()->get('AuthService')->getStorage()->read();

        $orders = $orderTable->getOrderByUser($userArr["id"]);

//var_dump($orders);

        $viewModel = new ViewModel(
                array('orders' => $orders)
        );
        return $viewModel;
    }

    public function createAction() {
        $form = $this->getServiceLocator()->get('ProductForm');
        $viewModel = new ViewModel(array('form' => $form));
        return $viewModel;
    }

    public function confirmAction() {
        $viewModel = new ViewModel();
        return $viewModel;
    }

    public function getFileUploadLocation() {
        // Fetch Configuration from Module Config
        $config = $this->getServiceLocator()->get('config');
        return $config['module_config']['upload_location'];
    }

    public function processAction() {

        //  var_dump($_POST);

        $upload = new Product();

        // var_dump($this->params()->fromPost('name',null));

        $uploadFile = $this->params()->fromFiles('imageupload');

        //var_dump($uploadFile);
        //  $form = $this->getServiceLocator()->get('ProductForm');

        $uploadPath = $this->getFileUploadLocation();
        echo "<br>" . $uploadPath;
        // Save Uploaded file    	

        $adapter = new \Zend\File\Transfer\Adapter\Http();
        $adapter->setDestination($uploadPath);
        echo "<br>" . $uploadFile['name'];
        if ($adapter->receive($uploadFile['name'])) {

            $exchange_data = array();
            $exchange_data['name'] = $this->params()->fromPost('name', null);
            $exchange_data['filename'] = $uploadFile['name'];
            $exchange_data['price'] = $this->params()->fromPost('price', null);
            $exchange_data['description'] = $this->params()->fromPost('description', null);

            //  var_dump($exchange_data);

            $upload->exchangeArray($exchange_data);
        }


        // Create product
        $this->createProduct($upload);

        /*
          return $this->redirect()->toRoute(NULL, array(
          'controller' => 'product',
          'action' => 'confirm'
          ));
         */
        return false;
    }

    protected function createProduct($upload) {

        $userTable = $this->getServiceLocator()->get('ProductTable');
        $userTable->saveProduct($upload);

        return true;
    }

    //message of admin part
    public function messageAction() {
        $message = "If you are interested in admin section write me: contact: blogbookhu@gmail.com";
        //return false;

        $viewModel = new ViewModel(
                array('message' => $message)
        );
        return $viewModel;
    }

}