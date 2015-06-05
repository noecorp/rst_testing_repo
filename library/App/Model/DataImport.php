<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class DataImport extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_DATA_IMPORT_ECS;


    public function doImport($productId = NULL) {
        
        if(!$productId) {
            
        }
        
    }
    
//    private function validateInfoByProductId($productId) {
//        
//    }
//    
//    private function getImportDataByProduct($pId) {
//        $sql = $this->select()
//                ->where('status=?',STATUS_PENDING)
//                ->
//    }
//    
//    private function ImportProcess(){
//        
//    }
//    

}