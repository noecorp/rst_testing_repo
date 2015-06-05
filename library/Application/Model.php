<?php
namespace Application;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
//use Zend\Db\Sql\Select;
//use Zend\Db\ResultSet\ResultSet;
/**
 * Description of Model
 *
 * @author Vikram
 */
class Model extends \Zend\Db\TableGateway\AbstractTableGateway 
{
    
    protected $_serviceLocator;
    protected $_table;
    protected $_dbAdapter;
    
    public function __construct(Adapter $adapter,$table='') {
        $this->_dbAdapter = $adapter;
        $this->_table = $table;
    }
    
    public function setTable($table)
    {
        $this->_table = $table;
    }
    
    public function getTableGateway()
    {
        return new TableGateway($this->_table,$this->_dbAdapter);
    }
    
    protected function getDbAdapter() {
        return $this->_dbAdapter;
    }
}
