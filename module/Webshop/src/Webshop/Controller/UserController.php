<?php

namespace Webshop\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Session\Container; // We need this when using sessions

class UserController extends AbstractActionController {

    protected $authservice;

    public function logoutAction() {
        $this->getAuthService()->getStorage()->clear();

        $user_session = new Container('webshop');
        $user_session->getManager()->destroy();
        return $this->redirect()->toRoute('webshop/product');
    }

    //login
    public function indexAction() {


        $message = "";
        $form = $this->getServiceLocator()->get('LoginForm');

        ///POST /////
        if ($this->getRequest()->isPost()) {
            $this->getAuthService()->getAdapter()->setIdentity($this->request->getPost('email'))->setCredential($this->request->getPost('password'));
            $result = $this->getAuthService()->authenticate();
            // Print the identity- username
            //echo $result->getIdentity() . "\n\n";
            $userData = $this->getAuthService()->getAdapter()->getResultRowObject();
            //  print_r($userData);

            if ($result->isValid()) {


                $this->getAuthService()->getStorage()->write(
                        array(
                            'id' => $userData->id,
                            'name' => $userData->name,
                            'email' => $userData->email,
                            // role hozzáadása
                            'role' => $userData->role,
                        )
                );

                //session 
                $user_session = new Container('webshop');
                //  $user_session->id = $userData->id;
                $user_session->name = $userData->name;
                //   $user_session->email = $userData->email;
                // role hozzáadása
                $user_session->role = $userData->role;

                if ($userData->role == 'admin') {
                    return $this->redirect()->toRoute(NULL, array(
                                'controller' => 'product',
                                'action' => 'message'
                    ));
                } else {
                    return $this->redirect()->toRoute(NULL, array(
                                'controller' => 'product',
                                'action' => 'index'
                    ));
                }
            } else {
                $message = "Username or password wrong. Please try again.";
            }
        }
        //////////


        $viewModel = new ViewModel(array('form' => $form, 'message' => $message));
        return $viewModel;
    }

    //Database Table Authentication¶
    public function getAuthService() {
        if (!$this->authservice) {

            $authService = $this->getServiceLocator()->get('AuthService');
            $this->authservice = $authService;
        }
        return $this->authservice;
    }

    public function confirmAction() {
        // $user_email = $this->getAuthService()->getStorage()->read();
        $user_session = new Container('webshop');
        $name = $user_session->name;
        $viewModel = new ViewModel(array(
            'name' => $name
        ));
        return $viewModel;
    }

}