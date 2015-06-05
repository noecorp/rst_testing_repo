<?php
/**
 * Model that manages the Currency
 *
 * @package Operation_Models
 * @copyright transerv
 */

class APISettings extends App_Model
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
    protected $_name = DbTable::TABLE_SETTINGS;
    
    
    public function updateSetting($param) {

        if ($param['type'] == '') {
            throw new Exception('Insufficient data to update');
            exit;
        }

        $param['value'] = ($param['value'] == 1) ? 1 : 0;
        $this->_db->update(DbTable::TABLE_SETTINGS, array('value' => $param['value']), 'type="' . $param['type'] . '"');
        return true;
    }

    public function checkAPIresponse($act = TXNTYPE_CARDHOLDER_REGISTRATION){
       
        $select = $this->_db->select()    
                ->from(DbTable::TABLE_SETTINGS.' as s',array('s.value','s.type'));               
                $select->where("s.type = '".SETTING_API_ECS."' OR s.type ='".SETTING_API_ISO."'");
                $select->where("s.status ='".STATUS_ACTIVE."'");
                $select->order('s.name ASC');
         
        $chk =   $this->_db->fetchAll($select); 
         
        
        if($act == TXNTYPE_CARD_RELOAD && $chk[1]['value'] == 0) // iso check
        {
           return  FALSE;
        }
        else {
            if($chk[0]['value'] == 0 || $chk[1]['value'] == 0){ // ecs_api and ecs_iso
                return FALSE;
               
                }
            else {
                return TRUE;
               
            }
        }
    }
    
}