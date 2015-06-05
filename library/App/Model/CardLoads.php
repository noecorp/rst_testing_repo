<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class CardLoads extends App_Model
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
    protected $_name = DbTable::TABLE_CARDLOADS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
   /*protected $_referenceMap = array(
        't_agent_details' => array(
            'columns' => 'agent_id',
            'refTableClass' => 't_agents',
            'refColumns' => 'id'
        ),
    );*/
    
    
    
    /*  getAgentTotalLoadReload function is responsible fetch data for 
     *  agent total laod, reload, remitter registration and remittance amount , 
     *  as params it will accept agent id and transaction date
     */
    
    public function getAgentTotalLoadReload($param){

        $agentId = isset($param['agent_id'])?$param['agent_id']:0;
        $date = isset($param['date'])?$param['date']:'';
        $dateFrom = isset($param['dateFrom'])?$param['dateFrom']:'';
        $dateTo = isset($param['dateTo'])?$param['dateTo']:'';
       

        if($date!='' || ($dateFrom!='' && $dateTo!='')){ 

            //Enable DB Slave
            $this->_enableDbSlave();
            $select =  $this->_db->select() ;
            $select->from(DbTable::TABLE_CARDLOADS.' as cl', array('sum(cl.amount) as total_agent_load_reload'));
            if($agentId>0)
               $select->where('cl.agent_id=?',$agentId);
            
            $select->where("cl.status='".FLAG_SUCCESS."'");         
            $select->where("(cl.txn_type='".TXNTYPE_FIRST_LOAD."') OR (cl.txn_type='".TXNTYPE_CARD_RELOAD."')");
            if($date!='')
               $select->where("DATE(cl.date_created) ='".$date."'"); 
            else if($dateFrom!='' && $dateTo!='')
                    $select->where("DATE(cl.date_created) BETWEEN '".$dateFrom."' AND '".$dateTo."'"); 
            
//           echo $select.'<br><br>'; 
         $row = $this->_db->fetchRow($select);
        //Disable DB Slave
        $this->_disableDbSlave();
        return $row;
       } else return '';
    }
   
    
     public function getAgentLoadReload($param, $page = 1, $paginate = NULL){
         // $param['programType'] not being used reight now, reqd. later
        $select = $this->sqlAgentLoadReload($param);
        return $this->_paginate($select, $page, $paginate); 
                     
    }
    
    public function getAllagentbyBankUnicode($bank_unicode){
        $select = $this->select();
        $select->setIntegrityCheck(false); 
        $select->from(DbTable::TABLE_AGENTS." as a",array('aid' => new Zend\Db\Sql\Expression('DISTINCT id')));
        $select->joinLeft(
            DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as apc", "apc.agent_id = a.id" ,array());
        $select->joinLeft(
            DbTable::TABLE_PRODUCTS." as tp", "apc.product_id = tp.id" ,array());
        $select->joinLeft(
            DbTable::TABLE_BANK." as b", "tp.bank_id = b.id" ,array()); 
        $select->where('b.unicode=?',$bank_unicode);  
        return $select;  
    }
     
    
    /* sqlAgentLoadReload function will return query for AgentLoadReload report 
     * it will accept the duration as in param array
     */
    
    public function sqlAgentLoadReload($param){ //crn
        
        $agentId = isset($param['agent_id'])?$param['agent_id']:0;
        $bank_unicode = isset($param['bank_unicode'])?$param['bank_unicode']:0;
        
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $crn = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn"); 
	
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_AGENTS." as a",array('id','concat(a.first_name," ",a.last_name) as agent_name','a.agent_code'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "a.id=ad.agent_id AND ad.status='".STATUS_ACTIVE."'", array('estab_city', 'estab_pincode'));
        $select->joinLeft(DbTable::TABLE_CARDLOADS." as cl", "a.id = cl.agent_id", array("txn_code", "amount", "txn_type", "DATE_FORMAT(cl.date_created, '%d-%m-%Y %h:%i:%s') as txn_date"));
        $select->joinLeft(DbTable::TABLE_CARDHOLDERS." as ch", "cl.cardholder_id = ch.id", array($crn, 'mobile_number'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS." as p", "cl.product_id = p.id", array('ecs_product_code'));
         
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel", "a.id = orel.to_object_id" ,array( ));
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as dis", "dis.id = orel.from_object_id" , 
                    array(
                        'dis.agent_code AS dist_code',' concat(dis.first_name," ",dis.last_name) as dist_name',
                ));
        $select->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION." as orel_sdis", "dis.id = orel_sdis.to_object_id" ,array());
        $select->joinLeft(
                DbTable::TABLE_AGENTS." as sdis", "sdis.id = orel_sdis.from_object_id" , 
                    array(
                        'sdis.agent_code AS sup_dist_code' ,'concat(sdis.first_name," ",sdis.last_name) as sup_dist_name'
                ));
        
        if($agentId>0) {
            $select->where('cl.agent_id=?',$agentId);
        } elseif($bank_unicode>0) {
            //SELECT a.id,a.agent_code,a.first_name,apc.id as acp_id,apc.product_id,p.bank_id FROM `t_agents` a LEFT JOIN `t_bind_agent_product_commission` apc ON apc.agent_id = a.id LEFT JOIN  t_products p ON apc.product_id = p.id WHERE p.bank_id = 3
            
            $select->joinLeft(
                DbTable::TABLE_BIND_AGENT_PRODUCT_COMMISSION." as apc", "apc.agent_id = a.id" ,array());
            $select->joinLeft(
                DbTable::TABLE_PRODUCTS." as tp", "apc.product_id = tp.id" ,array());
            $select->joinLeft(
                DbTable::TABLE_BANK." as b", "tp.bank_id = b.id" ,array());
            
            $select->where('b.unicode=?',$bank_unicode);
            
        }
        
        if(isset($param['programType']) && $param['programType']!='') {
            $select->where("p.program_type='".$param['programType']."'"); 
        }
        
        $select->where("cl.status='".FLAG_SUCCESS."'");         
        $select->where("(cl.txn_type='".TXNTYPE_FIRST_LOAD."') OR (cl.txn_type='".TXNTYPE_CARD_RELOAD."')");         

        if(isset($param['from']) && $param['from']!='')
            $select->where('cl.date_created>=?',$param['from']); 

        if(isset($param['to']) && $param['to']!='')
            $select->where('cl.date_created<=?',$param['to']); 

        $select->order('agent_name ASC');
        $select->order('cl.date_created ASC');
           
        return $select;
    }
    
    
    
    /* sqlAgentWiseLoads function will return query for AgentWiseLoads report 
     * it will accept the duration and agent id as in param array
     */
    
    public function sqlAgentWiseLoads($param){
	    $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
	    $crn = new Zend_Db_Expr("AES_DECRYPT(`ch`.`crn`,'".$decryptionKey."') as crn"); 
	
            $select = $this->select();
            $select->setIntegrityCheck(false);
            //$select->setIntegrityCheck(false);
            $select->from(DbTable::TABLE_CARDLOADS." as cl", array('cl.txn_code', 'cl.amount', 'cl.txn_type', 'cl.product_id', "DATE_FORMAT(cl.date_created, '%d-%m-%Y %h:%i:%s') as date_created"));              
            $select->joinLeft(DbTable::TABLE_CARDHOLDERS." as ch", "cl.cardholder_id = ch.id", array('concat(ch.first_name," ",ch.last_name) as cardholder_name', 'ch.mobile_number', $crn));
            //$select->joinLeft("t_cardholder_details as chd", "cl.cardholder_id = chd.cardholder_id", array(''));                    
            $select->joinLeft(DbTable::TABLE_PRODUCTS." as p", "cl.product_id = p.id", array('p.ecs_product_code'));                    
            $select->joinLeft(DbTable::TABLE_BANK." as b", "p.bank_id = b.id", array('b.name as bank_name'));                    
            $select->where("cl.status='".FLAG_SUCCESS."'");
            $select->where("(cl.txn_type='".TXNTYPE_FIRST_LOAD."') OR (cl.txn_type='".TXNTYPE_CARD_RELOAD."')");        
            //$select->where("cl.txn_type='".TXNTYPE_CARD_RELOAD."'"); 

            if(isset($param['agent_id']) && $param['agent_id']> 0)
                 $select->where('cl.agent_id=?',$param['agent_id']); 

            if(isset($param['from']) && $param['from']!='')
                $select->where('cl.date_created>=?',$param['from']); 

            if(isset($param['to']) && $param['to']!='')
                $select->where('cl.date_created<=?',$param['to']); 

            $select->order('cl.date_created DESC'); 
         return  $select;
    }
    
    public function getAgentWiseLoads($param, $page = 1, $paginate = NULL){
         // $param['programType'] not being used reight now, reqd. later
        $select = $this->sqlAgentWiseLoads($param);
        //echo $select->__toString(); exit;        
        
        return $this->_paginate($select, $page, $paginate,$numRecords = 10);
    }
    
     /* getAgentCardLoads() will return query for Agent Load Reload
     *  As Parms:- it will accept the query date and agent id
     */
    
    public function getAgentCardLoads($param){
        
        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $productId = isset($param['product_id'])?$param['product_id']:'';
        $date = isset($param['date'])?$param['date']:'';
        $txnType = isset($param['txn_type'])?$param['txn_type']:'';
        if($date=='')
            return array();
        
        
         $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            
            case $bankUnicodeArr['2']:
                
        
                $ratnakarRemitModel = new Corp_Ratnakar_Cardload();
                $detailsArr = $ratnakarRemitModel->getAgentCardLoads($param);
            
                break;
            default:
                    $select = $this->select();
                    $select->setIntegrityCheck(false);
                    $select->from(DbTable::TABLE_CARDLOADS." as cl",array("cl.txn_code as transaction_ref_no", "cl.amount as transaction_amount"));
                    $select->where("cl.status='".FLAG_SUCCESS."'");         
                    $select->where("DATE(cl.date_created) ='".$date."'");         
                    $select->where("cl.txn_type='".$txnType."'");         

                    if($agentId>=1)
                        $select->where('cl.agent_id=?',$agentId); 

                    if($productId>=1)
                        $select->where('cl.product_id=?',$productId); 

                    $select->order('cl.date_created ASC');
            //        echo $select.'<br><br>';

                    $detailsArr = $this->_db->fetchAll($select);
                            break;
                    }
                    
                    return $detailsArr;
       
    }
    
     /* exportAgentLoadReload function will query the AgentLoadReload report data 
     * for export to csv purpose
     */
    
    public function exportAgentLoadReload($param){
         // $param['programType'] not being used reight now, reqd. later
        $select = $this->sqlAgentLoadReload($param);        
        
        return  $this->_db->fetchAll($select);      
    }
    
    /* exportAgentWiseLoads function will query the AgentLoadReload report data for export to csv purpose
     * it will accept the duration and agent id as in param array
     */
    
    public function exportAgentWiseLoads($param){
        // $param['programType'] not being used reight now, reqd. later
        $select = $this->sqlAgentWiseLoads($param);        
        
        return  $this->_db->fetchAll($select);                      
    }
    
    /*  getAgentTotalLoadsCounts function is responsible to fetch data for agent total loads or reloads and its count
     *  as params it will accept agent id(optional), txn type and transaction date
     */
    
    public function getAgentTotalLoadsCounts($param){

        $agentId = isset($param['agent_id'])?$param['agent_id']:'';
        $date = isset($param['date'])?$param['date']:'';
        $txnType = isset($param['txn_type'])?$param['txn_type']:'';
        
        if($date!=''){ 
            
            $select =  $this->_db->select() ;
            $select->from(DbTable::TABLE_CARDLOADS.' as cl', array('sum(cl.amount) as total_agent_loads', 'count(cl.id) as total_agent_loads_count'));
            $select->where("cl.status='".FLAG_SUCCESS."'");         
            $select->where("DATE(cl.date_created) ='".$date."'"); 
            $select->where("(cl.txn_type='".$txnType."')");
            if($agentId > 0)
               $select->where('cl.agent_id=?',$agentId);
            
           //echo $select.'<br><br>'; exit;
         return $this->_db->fetchRow($select);
       } else return '';
    }
    
    /* sqlCardholderActivations function will return query for Cardholder Activations report 
     * it will accept the duration as in param array
     */
    
    public function sqlCardholderActivations($param){
        $fromDate = isset($param['from'])?$param['from']:'';
        $toDate = isset($param['to'])?$param['to']:'';
                
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $crn = new Zend_Db_Expr("AES_DECRYPT(`chd`.`crn`,'".$decryptionKey."') as crn");
	
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_CARDLOADS." as cl", array('cl.txn_code', 'cl.amount', 'cl.txn_type', 'cl.product_id', "DATE_FORMAT(cl.date_created, '%d-%m-%Y %h:%i:%s') as date_created"));              
        $select->joinLeft(DbTable::TABLE_CARDHOLDER_DETAILS." as chd", "cl.cardholder_id = chd.cardholder_id", array('concat(chd.first_name," ",chd.last_name) as cardholder_name', 'chd.mobile_number', $crn, 'chd.address_line1', 'chd.address_line2', 'chd.city', 'chd.district', 'chd.state', 'chd.arn'));
        $select->joinLeft(DbTable::TABLE_AGENT_DETAILS." as ad", "cl.agent_id = ad.agent_id AND ad.status = '".STATUS_ACTIVE."'", array('concat(ad.first_name," ",ad.last_name) as agent_name', 'ad.estab_city as agent_city', 'ad.estab_pincode as agent_pincode'));                    
        $select->joinLeft(DbTable::TABLE_AGENTS." as a", "cl.agent_id = a.id", array('a.agent_code'));
        $select->joinLeft(DbTable::TABLE_PRODUCTS." as p", "cl.product_id = p.id", array('p.ecs_product_code', 'p.name as product_name'));                    
        $select->joinLeft(DbTable::TABLE_BANK." as b", "p.bank_id = b.id", array('b.name as bank_name'));                    
        $select->where("cl.status='".FLAG_SUCCESS."'");
        $select->where("cl.txn_type='".TXNTYPE_FIRST_LOAD."'");        

        if(isset($param['agent_id']) && $param['agent_id']> 0)
             $select->where('cl.agent_id=?',$param['agent_id']); 

        if($fromDate != '')
            $select->where('cl.date_created>=?', $fromDate); 

        if($toDate != '')
            $select->where('cl.date_created<=?', $toDate); 

        $select->order('cardholder_name ASC');
//          echo $select; //exit;
         return  $select;
    }
    
    public function getCardholderActivations($param, $page = 1, $paginate = NULL){
          // $param['programType'] not being used reight now, reqd. later      
        $select = $this->sqlCardholderActivations($param);
        //echo $select->__toString();exit;        
       
        return $this->_paginate($select, $page, $paginate);    
    }
    
    /* exportCardholderActivations function will query the Cardholder Activations report data 
     *  for export to csv purpose
     */
    
    public function exportCardholderActivations($param){
        // $param['programType'] not being used reight now, reqd. later
        $select = $this->sqlCardholderActivations($param);        
        
        return  $this->_db->fetchAll($select);                      
    }
    
    public function getAgentLoadsReloadsCountAll($param) { 
        $agentId = isset($param['agent_id']) ? $param['agent_id'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $txnType = isset($param['txn_type']) ? $param['txn_type'] : '';
        if ($from != '' && $to != '') {
            $select = $this->_db->select();
            $select->from(
                        DbTable::TABLE_CARDLOADS . ' as cl', 
                        array(
                            'sum(cl.amount) as total_agent_loads', 
                            'count(cl.id) as total_agent_loads_count',
                            'cl.date_created', 'DATE_FORMAT(cl.date_created,"%d-%m-%Y") AS txn_date'
                        )); 
            $select->where("cl.status='" . FLAG_SUCCESS . "'");            
            $select->where("DATE(cl.date_created) BETWEEN '". $from ."' AND '". $to ."'");
            if ($txnType != '') { 
                $select->where("(cl.txn_type='" . $txnType . "')");
            } else{
                $select->where("cl.txn_type IN ('". TXNTYPE_FIRST_LOAD."','".TXNTYPE_CARD_RELOAD."')");  
            }
            
            if ($agentId > 0) {
                $select->where('cl.agent_id=?', $agentId);
            }
            $select->group('DATE_FORMAT(cl.date_created, "%Y-%m-%d")'); 
            return $this->_db->fetchAll($select); 
        } else
            return '';
    }
}