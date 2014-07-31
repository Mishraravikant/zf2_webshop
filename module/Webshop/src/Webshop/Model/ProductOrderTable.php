<?php

namespace Webshop\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class ProductOrderTable {

    protected $tableGateway;
    protected $productTableGateway;
    protected $adapter;

    public function __construct(TableGateway $tableGateway, TableGateway $productTableGateway, Adapter $adapter) {
        $this->tableGateway = $tableGateway;
        $this->productTableGateway = $productTableGateway;
        $this->adapter = $adapter;
    }

    public function saveOrder(ProductOrder $order) {
        $data = array(
            'store_product_id' => $order->store_product_id,
            'qty' => $order->qty,
            'total' => $order->total,
            'status' => $order->status,
            'name' => $order->name,
            'email' => $order->email,
            'user_id' => $order->user_id,
        );

        $id = (int) $order->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
        } else {
            if ($this->getOrder($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Order ID does not exist');
            }
        }
    }

    public function fetchAll() {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getAllOrder() {
        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('id', 'qty', 'name', 'email', 'stamp'));
        $sqlSelect->join('product', 'product.id = product_orders.store_product_id', array('filename', 'product_name' => 'name'), 'inner');

        $rowset = $this->tableGateway->selectWith($sqlSelect);

        return $rowset;
    }

    public function getOrder($orderId) {
        $orderId = (int) $orderId;
        $rowset = $this->tableGateway->select(array('id' => $orderId));
        $order = $rowset->current();
        if (!$order) {
            throw new \Exception("Could not find row $orderId");
        }

        $productId = $order->store_product_id;

        $prodRowset = $this->productTableGateway->select(array('id' => $productId));
        $product = $prodRowset->current();

        if (!empty($product)) {
            $order->setProduct($product);
        }
        return $order;
    }

    public function getOrderByUser($userId) {

        $sqlSelect = $this->tableGateway->getSql()->select();
        $sqlSelect->columns(array('qty', 'total'));
        $sqlSelect->join('product', 'product.id = product_orders.store_product_id', array('filename', 'product_name' => 'name', 'price'), 'inner');
        $sqlSelect->where(array('user_id ' => "$userId"));
        //echo $sqlSelect->getSqlString(); 
        $rowset = $this->tableGateway->selectWith($sqlSelect);

        $orders = $rowset;
        //  echo "szám:". $rowset->count();

        return $orders;
    }

    public function deleteOrder($orderId) {
        $this->tableGateway->delete(array('id' => $orderId));
    }

    public function getProduct($orderId) {
        $orderId = (int) $orderId;
        $order = $this->getOrder($orderId);
        $productId = $order->store_product_id;

        $rowset = $this->productTableGateway->select(array('id' => $productId));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $orderId");
        }
        return $row;
    }

}
