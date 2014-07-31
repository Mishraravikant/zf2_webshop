<?php

namespace Webshop\Model;

//use Zend\Db\Adapter\Adapter;
//use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class ProductTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
    }

    protected function createProduct(array $data) {
        $sm = $this->getServiceLocator();
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();

        $resultSetPrototype->setArrayObjectPrototype(new
                \Store\Model\Product);
        $tableGateway = new \Zend\Db\TableGateway\TableGateway('product', $dbAdapter, null, $resultSetPrototype);
        $product = new Product();
        $product->exchangeArray($data);
        $productTable = new ProductTable($tableGateway);
        $productTable->saveProduct($product);
        return true;
    }

    public function saveProduct(Product $product) {

        if ($product->filename == "nofile") {
            $data = array(
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            );
        } else {
            $data = array(
                'filename' => $product->filename,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
            );
        }

        $id = (int) $product->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getProduct($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Product ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getProduct($id) {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getProductByName($productName) {
        $rowset = $this->tableGateway->select(array('name' =>
            $productName));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $ productEmail");
        }

        return $row;
    }

    public function deleteProduct($id) {
        $this->tableGateway->delete(array('id' => $id));
    }

}