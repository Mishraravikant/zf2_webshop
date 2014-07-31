<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Webshop\Controller\Index' => 'Webshop\Controller\IndexController',
            'Webshop\Controller\Product' => 'Webshop\Controller\ProductController',
            'Webshop\Controller\User' => 'Webshop\Controller\UserController',
            'Webshop\Controller\Productadmin' => 'Webshop\Controller\ProductadminController',
            'Webshop\Controller\Register' => 'Webshop\Controller\RegisterController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'webshop' => array(
                'type' => 'Literal',
                'options' => array(
                    // How to Make Your Module Default in Zend Framework 2
                    'route' => '/',
                    //'route' => '/webshop',
                    'defaults' => array(
// Change this value to reflect the namespace in which
// the controllers for your module are found
                        '__NAMESPACE__' => 'Webshop\Controller',
                        'controller' => 'Product',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.

                    'product' => array(
                        'type' => 'Segment',
                        'options' => array(
                            //  'route' => '/product[/:action]/productid[/:productid]',
                            'route' => '/product[/:action]/',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Webshop\Controller',
                                'controller' => 'Product',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'productadmin' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/productadmin[/:action]/',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Webshop\Controller',
                                'controller' => 'Productadmin',
                                'action' => 'productmanagement',
                            ),
                        ),
                    ),
                    'user' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/user[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Webshop\Controller',
                                'controller' => 'User',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'register' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/register[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Webshop\Controller\Register',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_path_stack' => array(
            'webshop' => __DIR__ . '/../view',
        ),
        'template_map' => array(           
            //debug
            //'layout/layout' => __DIR__ . '/../view/layout/default-layout_dev.phtml',
            //public
            'layout/layout' => __DIR__ . '/../view/layout/default-layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
    ),
    //from Application module
    /* 'view_manager' => array(
      'display_not_found_reason' => true,
      'display_exceptions' => true,
      'doctype' => 'HTML5',
      'not_found_template' => 'error/404',
      'exception_template' => 'error/index',
      'template_map' => array(
      'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
      'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
      'error/404' => __DIR__ . '/../view/error/404.phtml',
      'error/index' => __DIR__ . '/../view/error/index.phtml',
      ),
      'template_path_stack' => array(
      __DIR__ . '/../view',
      ),
      ),
     */
    // MODULE CONFIGURATIONS
    'module_config' => array(
        'image_upload_location' => __DIR__ . '/../data/images',
        'upload_location' => __DIR__ . '/../data/uploads',
    ),
);
