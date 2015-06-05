<?php
/**
 * Manages the Unicode
 *
 * @package Unicode
 * @copyright transerv
 */

class Unicode extends App_Model
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
    protected $_name = DbTable::TABLE_UNICODE;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Group';
    
    
    public $_BANK_UNICODE; 
    public $_PRODUCT_UNICODE; 
    public $_UNICODE; 
    public $_PROGRAM_TYPE;
    private $_id;
    
    /**
     * 
     * @access public
     * @return array
     */
    public function generateUnicode(){
        $programTypes = Zend_Registry::get('PROGRAM_TYPE');
        if(!isset($this->_PROGRAM_TYPE) || $this->_PROGRAM_TYPE =='' || !array_key_exists($this->_PROGRAM_TYPE, $programTypes)) {
            throw new Exception('Invalid Program Type');
        }
        
        if(!isset($this->_BANK_UNICODE) || $this->_BANK_UNICODE =='') {
            throw new Exception('Invalid Bank Unicode');
        }
        
        if(!isset($this->_PRODUCT_UNICODE) || $this->_PRODUCT_UNICODE =='') {
            throw new Exception('Invalid Product Unicode');
        }
        //List of Program type that require CRN from Processor
        if($this->_PROGRAM_TYPE == PROGRAM_TYPE_MVC ) { //|| $this->_PROGRAM_TYPE == PROGRAM_TYPE_CORP) {
            $crnInfo = $this->getFreeCRN();
            if(empty($crnInfo) || !isset($crnInfo['id'])) {
                throw new Exception('CRN/Unicode out of list');            
            } else {
                $crnInfo = Util::toArray($crnInfo);                
                $this->_id = $crnInfo['id'];
            }
        }
        if($this->setupUnicode() === true) {
            $this->saveUnicode();
            return true;
        } else {
           //return false;  
           throw new Exception('Bank Unicode or Product Unicode not found'); 
        }
   }
    
    /**
     * setupUnicode
     * Setup Unicode used to generate unique unicode
     * @return boolean
     * @throws Exception
     */
    private function setupUnicode()
    {
        unset($this->_UNICODE);
        //$unicodeArray = Zend_Registry::get("UNICODE_GLOBAL");
        //if(isset($unicodeArray[$this->_BANK_UNICODE][$this->_PRODUCT_UNICODE]) 
        //                    && $unicodeArray[$this->_BANK_UNICODE][$this->_PRODUCT_UNICODE] != '') {
            //Validate Length
            /*if(strlen($unicodeArray[$this->_BANK_UNICODE][$this->_PRODUCT_UNICODE]) != UNICODE_INITIAL_FIXED_LENGTH) {
                throw new Exception('Invalid Unicode definition');
            } */
            $unicodeInitials = $this->getUnicodeInitials($this->_BANK_UNICODE, $this->_PRODUCT_UNICODE);
            //print $this->_BANK_UNICODE . ' : '. $this->_PRODUCT_UNICODE . '<br />';
            //print $unicodeInitials;exit('**');       
            if($unicodeInitials =='') {
                throw new Exception('Invalid Unicode initial definition');
            }
            $this->_UNICODE = $unicodeInitials . rand('10000000', '99999999');
            if($this->validateGeneratedUnicode() === false) {
                self::setupUnicode();
            }
            return true;
       // }
        //return false;
                            
    }
    
    /**
     * validateGeneratedUnicode
     * Validate Generated Unicode into DB, This will help to make it unique
     * @return boolean
     */
    private function validateGeneratedUnicode() {
        $unicodeData = $this->fetchRow($this->select()
                                ->where(" unicode = '".$this->_UNICODE."' ")
        );
       
        if(!empty($unicodeData)) {
            return false;
        }
        return true;
    }
    
    /*
     * getUnicode
     * Function is used to return newly generated UNICODE
     */
    public function getUnicode() {
        if(isset($this->_UNICODE) && $this->_UNICODE != '') {
            return $this->_UNICODE;
        }
    }
    
    public function getUnicodeInfo() {
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
	
	//return $this->fetchRow(
                $sql = $this->select()
			->from($this->_name,array('id','bank_unicode','product_unicode',$crn,'unicode','status','date_added','date_updated'))
                               ->where("unicode='".$this->_UNICODE."'")
                               ->limit(1);
                        //);        
                //echo $sql;
                return $this->fetchRow($sql);
    }

    /**
     * saveUnicode
     * Method to save Unicode
     */
    private function saveUnicode() {
        if($this->_PROGRAM_TYPE == PROGRAM_TYPE_REMIT) {
        $this->insert(array(
                'bank_unicode'  => $this->_BANK_UNICODE,
                'product_unicode'  => $this->_PRODUCT_UNICODE,
                'unicode'  => $this->_UNICODE,
                'status'  => STATUS_FREE,
                'date_added'  => new Zend_Db_Expr('NOW()'),
                'date_updated'  => new Zend_Db_Expr('NOW()')
             )
          );
        } 
        elseif($this->_PROGRAM_TYPE == PROGRAM_TYPE_CORP) {
//            $this->update(array(
//                'unicode'   => $this->_UNICODE,
//                    ), 
//               " id = '".$this->_id."' ");      
        $this->insert(array(
                'bank_unicode'  => $this->_BANK_UNICODE,
                'product_unicode'  => $this->_PRODUCT_UNICODE,
                'unicode'  => $this->_UNICODE,
                'status'  => STATUS_FREE,
                'date_added'  => new Zend_Db_Expr('NOW()'),
                'date_updated'  => new Zend_Db_Expr('NOW()')
             )
          );            
        } 
        elseif($this->_PROGRAM_TYPE == PROGRAM_TYPE_MVC) {

            $this->update(array(
                'unicode'   => $this->_UNICODE,
                    ), 
               // "( bank_unicode = '".$this->_BANK_UNICODE."' AND product_unicode = '".$this->_PRODUCT_UNICODE."' ) 
               // OR  ( bank_unicode = '".$this->_BANK_UNICODE."' AND ISNULL(product_unicode) 
               //" ( status = '".STATUS_FREE."' )
               // AND ISNULL(unicode)
               // AND 
               " id = '".$this->_id."' ");
            }
    }

    /**
     * getFreeCRN
     * Get Free CRN on the basis of BANK_UNICODE/PRODUCT_UNICODE or BANK_UNICODE
     * @return type
     */
    public function getFreeCRN(){
	$decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $crn = new Zend_Db_Expr("AES_DECRYPT(`crn`,'".$decryptionKey."') as crn");
	
        $unicodeData = $this->fetchRow($sql = $this->select()
				->from($this->_name,array('id','bank_unicode','product_unicode',$crn,'unicode','status','date_added','date_updated'))
                               ->where(" status = '".STATUS_FREE."' ") 
                               ->where(" ( bank_unicode = '".$this->_BANK_UNICODE."' AND product_unicode = '".$this->_PRODUCT_UNICODE."' ) OR  ( bank_unicode = '".$this->_BANK_UNICODE."' AND ISNULL(product_unicode) )")
                               ->where("ISNULL(unicode)")
                               ->order("product_unicode desc")
                               ->limit(1));        
        return $unicodeData;
    }
    
    /**
     * setUsedStatus
     * Method to set UNICODE Status as Used
     * @return type
     */
    public function setUsedStatus(){
        return $this->update(array(
                    'status'   => STATUS_USED,
                    ), 
               " unicode = '".$this->_UNICODE."' ");
    }
    
    
    public function validateCRN($crn) {
        if($crn == '') {
            throw new Exception('Invalid/Blank CRN Provided');
        }
	$encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $crn = new Zend_Db_Expr("AES_ENCRYPT('".$crn."','".$encryptionKey."')"); 
        $sql = $this->select()
                               ->where(" status = ? ",STATUS_FREE) 
                               ->where(" bank_unicode = ?",$this->_BANK_UNICODE)
                               //->where(" product_unicode = ? ",$this->_PRODUCT_UNICODE)
                               ->where(" crn = ? ",$crn)
                               ->limit(1);        
        if(isset($this->_PRODUCT_UNICODE) && $this->_PRODUCT_UNICODE != '') {
            $sql->where(" product_unicode = ? ",$this->_PRODUCT_UNICODE);
        }
        $unicodeData = $this->fetchRow($sql);
        if(empty($unicodeData)) { //Empty means no record found!!!
            return true;
        }      
        
        return false;
    }
    
    public function addUnicodeCRN ( $crns ) {
        
        if (empty($crns)) {
            throw new Exception("CRN not found to add");
        }
 
        try {
            $this->_db->beginTransaction();
            //Start Transaction
            foreach ($crns as $val) {  
                if($this->validateCRN($val['crn'])) {
		    $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
		    $val['crn'] = new Zend_Db_Expr("AES_ENCRYPT('".$val['crn']."','".$encryptionKey."')"); 
                    $unicodeCrnData = array(
                        'crn' => $val['crn'], 
                        'status' => STATUS_FREE,
                        'bank_unicode'  => $this->_BANK_UNICODE,
                        'product_unicode'  => $this->_PRODUCT_UNICODE
                    );
                    $this->_db->insert(DbTable::TABLE_ECS_CRN, $val);
                    $this->_db->insert(DbTable::TABLE_UNICODE, $unicodeCrnData);
                } else {
                    throw new Exception('Duplicate CRN Provided:' . $val['crn']);
                    //print 'INvalid CRN Provided';exit;
                    return false;
                }
            }

            // End Transaction
            $this->_db->commit();
            //return true;
        } catch (Exception $e) {
            //echo '<pre>';print_r($e);print $e->getMessage();exit('hhhhh');
            throw new Exception($e->getMessage());
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            return false;
        }
        
        return true;
    }    
    
    /**
     * getUnicodeInitials - Get Unicode initials 
     * @return unicode Initials/FALSE
     */
    private function getUnicodeInitials() {
        $sql =      $this->_db->select()
                           ->from(DbTable::TABLE_UNICODE_CONF,array('unicode_initials'))
                           ->where(" bank_unicode = ?",$this->_BANK_UNICODE)
                           ->limit(1);    
        if(isset($this->_PRODUCT_UNICODE) && $this->_PRODUCT_UNICODE != '') {
                   $sql->where(" product_unicode = ? ",$this->_PRODUCT_UNICODE);
        }           
        $data = $this->_db->fetchRow($sql);
        if(isset($data['unicode_initials']) && $data['unicode_initials'] != '') {
            return $data['unicode_initials'];
        }
        return false;
    }
}
