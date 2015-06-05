<?php

namespace Corporate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Corporate\Form\CorporateUser\CorporateUserForm as CorporateUserForm;
use Zend\Authentication\Result;

class NewcorporateuserController extends \Application\Controller {

    public function loginAction() {
    	echo "sdfdsfsdf"; exit;
        parent::layout('layout/login');
        $errorType = NULL;
        $form = new CorporateUserForm();
        return new ViewModel(array(
            'form' => $form
        ));
    }

    public function logoutAction() {
        echo "12345"; exit;
    }

}