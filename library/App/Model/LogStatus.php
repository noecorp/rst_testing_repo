<?php
/**
 * LogStatus that manages the Log status updations in the application
 * @package Core
 * @copyright transerv
 */

class LogStatus extends App_Model
{

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    //protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_CHANGE_STATUS_LOG;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_CardholderUser';

    
    /* log function will add change status log details in t_change_status_log 
     * it will accept the many params in $param array argument, e.g.. status new, status old, remarks etc....
     */
    
    public function log($param)
    {
        $ip = $this->formatIpAddress(Util::getIP());
        
        if($param['status_old']=='' || $param['status_new']==''){
            throw new Exception('Insufficient data found for t_change_status_log table'); exit;
        }
        
       try {
        //$recipients = (is_array($inputArray['to'])) ? implode(", ", $inputArray['to']) : $inputArray['to'];
         
        $data = array(
                        'cardholder_id'        => isset($param['cardholder_id'])?$param['cardholder_id']:0,
                        'agent_id'             => isset($param['agent_id'])?$param['agent_id']:0,
                        'ops_id'               => isset($param['ops_id'])?$param['ops_id']:0,
                        'bank_id'              => isset($param['bank_id'])?$param['bank_id']:0,
                        'beneficiary_id'       => isset($param['beneficiary_id'])?$param['beneficiary_id']:0,
                        'kotak_beneficiary_id'       => isset($param['kotak_beneficiary_id'])?$param['kotak_beneficiary_id']:0,
                        'ratnakar_beneficiary_id'       => isset($param['ratnakar_beneficiary_id'])?$param['ratnakar_beneficiary_id']:0,
                        'by_agent_id'          => isset($param['by_agent_id'])?$param['by_agent_id']:0,
                        'by_ops_id'            => isset($param['by_ops_id'])?$param['by_ops_id']:0,
                        'ip'                   => $ip,
                        'remarks'              => isset($param['remarks'])?$param['remarks']:'',
                        'status_old'           => $param['status_old'],
                        'status_new'           => $param['status_new'],                
                        'date_created'         => new Zend_Db_Expr('NOW()')
        );
        
        return $this->_db->insert(DbTable::TABLE_CHANGE_STATUS_LOG,$data); 
       }
       catch(Exception $e ) {
           // echo "<pre>";print_r($e);exit;
           //Log Error
           App_Logger::log($e->getMessage(), Zend_Log::ERR);
           return false;
        }
    }
    
    
}