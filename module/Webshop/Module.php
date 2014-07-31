<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonModule for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Webshop;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Db\ResultSet\ResultSet;
use Webshop\Model\Product;
use Webshop\Model\ProductTable;
use Webshop\Model\ProductOrder;
use Webshop\Model\ProductOrderTable;
use Webshop\Model\User;
use Webshop\Model\UserTable;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container; // We need this when using sessions
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

//use Zend\Module\Consumer\AutoloaderProvider, Zend\EventManager\StaticEventManager;

class Module implements AutoloaderProviderInterface {

    private $app;
    private $serviceManager;

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    /*
      public function onBootstrap(MvcEvent $e)
      {
      // You may not need to do this if you're doing it elsewhere in your
      // application
      $eventManager        = $e->getApplication()->getEventManager();
      $moduleRouteListener = new ModuleRouteListener();
      $moduleRouteListener->attach($eventManager);
      }
     */

    public function onBootstrap($e) {


        $this->app = $e->getApplication();
        $this->serviceManager = $this->app->getServiceManager();

        ////ACL//////////
        $this->initAcl($e);
        $e->getApplication()->getEventManager()->attach('route', array($this, 'checkAcl'));

        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sharedEventManager = $eventManager->getSharedManager(); // The shared event manager

        $sharedEventManager->attach(__NAMESPACE__, MvcEvent::EVENT_DISPATCH, function($e) {
                    $controller = $e->getTarget(); // The controller which is dispatched
                    $controllerName = $controller->getEvent()->getRouteMatch()->getParam('controller');


                    $fooArr = $this->serviceManager->get('AuthService')->getStorage()->read();
                    if (!empty($fooArr) && $fooArr["role"] == "admin") {
                       // $controller->layout('layout/admin');
                    }
                });
    }

    //ACL
    public function initAcl(MvcEvent $e) {

        $acl = new \Zend\Permissions\Acl\Acl();


        $roleGuest = new Role('guest');
        $acl->addRole($roleGuest);
        $acl->addRole(new Role('user'), $roleGuest);
        $acl->addRole(new Role('admin'), 'user');

        $acl->addResource(new Resource('User'));
        $acl->addResource(new Resource('Product'));
        $acl->addResource(new Resource('Productadmin'));

        // Guest may only view content - allow($roles, $resources, $privileges)
        $acl->allow($roleGuest, 'User');
        //$acl->allow($roleGuest, 'Product', 'index');
        $acl->allow($roleGuest, 'Product', array('index', 'view'));

        $acl->allow('user', 'Product', array('shoppingCart', 'shoppingCartContent'));
        $acl->allow('admin', 'Product');
        $acl->allow('admin', 'Productadmin');

        $e->getViewModel()->acl = $acl;
    }

    public function checkAcl(MvcEvent $e) {

        $controller = $e->getRouteMatch()->getParam("controller");
        $action = $e->getRouteMatch()->getParam('action');
        // echo " controller: " . $controller . " | ";

        $fooArr = $this->serviceManager->get('AuthService')->getStorage()->read();
        // echo "role:" . $fooArr["role"];

        if (!empty($fooArr)) {
            $userRole = $fooArr["role"];
        } else {
            $userRole = 'guest';
        }

        if ($e->getViewModel()->acl->hasResource($controller) && !$e->getViewModel()->acl->isAllowed($userRole, $controller, $action)) {
            $response = $e->getResponse();

            $url = $e->getRouter()->assemble(array('action' => 'index'), array('name' => 'webshop/user'));
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);
            $response->sendHeaders();

            exit;
        }
    }

    public function getServiceConfig() {
        return array(
            'abstract_factories' => array(),
            'aliases' => array(),
            'factories' => array(
                // SERVICES

                'AuthService' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'user', 'email', 'password', 'MD5(?)');

                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    return $authService;
                },
// DB
                'ProductTable' => function($sm) {
                    $tableGateway = $sm->get('ProductTableGateway');
                    $table = new ProductTable($tableGateway);
                    return $table;
                },
                'ProductTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Product());

                    return new TableGateway('product', $dbAdapter, null, $resultSetPrototype);
                },
                'ProductOrdersTable' => function($sm) {
                    $tableGateway = $sm->get('ProductOrdersTableGateway');
                    $productTableGateway = $sm->get('ProductTableGateway');
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ProductOrderTable($tableGateway, $productTableGateway, $dbAdapter);
                    return $table;
                },
                'ProductOrdersTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ProductOrder());
                    return new TableGateway('product_orders', $dbAdapter, null, $resultSetPrototype);
                },
                'UserTable' => function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                'UploadTable' => function($sm) {
                    $tableGateway = $sm->get('UploadTableGateway');
                    $uploadSharingTableGateway = $sm->get('UploadSharingTableGateway');
                    $table = new UploadTable($tableGateway, $uploadSharingTableGateway);
                    return $table;
                },
                'UploadTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Upload());
                    return new TableGateway('uploads', $dbAdapter, null, $resultSetPrototype);
                },
                'UploadSharingTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('uploads_sharing', $dbAdapter);
                },
                'ImageUploadTable' => function($sm) {
                    $tableGateway = $sm->get('ImageUploadTableGateway');
                    $table = new ImageUploadTable($tableGateway);
                    return $table;
                },
                'ImageUploadTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ImageUpload());
                    return new TableGateway('image_uploads', $dbAdapter, null, $resultSetPrototype);
                },
// FORMS
                'LoginForm' => function ($sm) {
                    $form = new \Webshop\Form\LoginForm();
                    $form->setInputFilter($sm->get('LoginFilter'));
                    return $form;
                },
                'ProductForm' => function ($sm) {
                    $form = new \Webshop\Form\ProductForm();
                    $form->setInputFilter($sm->get('ProductFilter'));
                    return $form;
                },
                'BasketForm' => function ($sm) {
                    $form = new \Webshop\Form\BasketForm();
                    return $form;
                },
                'RegisterForm' => function ($sm) {
                    $form = new \Webshop\Form\RegisterForm();
                    $form->setInputFilter($sm->get('RegisterFilter'));
                    return $form;
                },
                'UserEditForm' => function ($sm) {
                    $form = new \Webshop\Form\UserEditForm();
                    $form->setInputFilter($sm->get('UserEditFilter'));
                    return $form;
                },
                'UploadForm' => function ($sm) {
                    $form = new \Webshop\Form\UploadForm();
                    return $form;
                },
                'UploadEditForm' => function ($sm) {
                    $form = new \Webshop\Form\UploadEditForm();
                    return $form;
                },
                'UploadShareForm' => function ($sm) {
                    $form = new \Webshop\Form\UploadShareForm();
                    return $form;
                },
                'ImageUploadForm' => function ($sm) {
                    $form = new \Webshop\Form\ImageUploadForm();
                    $form->setInputFilter($sm->get('ImageUploadFilter'));
                    return $form;
                },
                'MultiImageUploadForm' => function ($sm) {
                    $form = new \Webshop\Form\MultiImageUploadForm();
                    return $form;
                },
// FILTERS 
                'LoginFilter' => function ($sm) {
                    return new \Webshop\Form\LoginFilter();
                },
                'ProductFilter' => function ($sm) {
                    return new \Webshop\Form\ProductFilter();
                },
                'RegisterFilter' => function ($sm) {
                    return new \Webshop\Form\RegisterFilter();
                },
                'UserEditFilter' => function ($sm) {
                    return new \Webshop\Form\UserEditFilter();
                },
                'ImageUploadFilter' => function ($sm) {
                    return new \Webshop\Form\ImageUploadFilter();
                },
            ),
            'invokables' => array(),
            'services' => array(),
            'shared' => array(),
        );
    }

}
