<?php
namespace Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Db\Adapter\Adapter;
/**
 * Description of Model
 *
 * @author Vikram
 */
class Controller extends AbstractActionController {
    
    public function getModel($class) {
        $sm = $this->getServiceLocator()->get('Config');
        $model = new $class(new Adapter($sm['db']));
        return $model;
    }

}
