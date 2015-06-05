<?php
namespace Corporate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MyController extends AbstractActionController
{
    public function indexAction()
    { echo "sdfsdf"; exit;
        return new ViewModel();
    }
    
    public function abcAction()
    {echo "123456"; exit;
        return new ViewModel();
    }
}
