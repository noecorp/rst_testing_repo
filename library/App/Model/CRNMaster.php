<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class CRNMaster extends App_Model {

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
    protected $_name = DbTable::TABLE_CRN_MASTER;
    
    public function insertMasterCRN($dataArr) {
        if(!empty($dataArr) && isset($dataArr['card_number'])) {
            $cardNumber = isset($dataArr['card_number'])?trim($dataArr['card_number']):'';
            if($cardNumber!=''){
                $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
                $dataArr['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            }
            $user = Zend_Auth::getInstance()->getIdentity();
            $data = array(
                'card_number' => $dataArr['card_number'],
                'card_pack_id' => $dataArr['card_pack_id'],
                'member_id' => $dataArr['member_id'],
                'status' => $dataArr['status'],
                'file' => $dataArr['file'],
                'by_ops_id' => $user->id,
                'product_id' => $dataArr['product_id'],
                'date_expiry' => $dataArr['date_expiry'],
                'date_created' => new Zend_Db_Expr('NOW()')
            );
            //echo "<pre>";print_r($data);exit;
            return $this->insert($data);
        } 
        return false;
    }
    
    public function checkDuplicate($dataArr) {
        $crnKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$crnKey."') ");
        $sql = $this->select()
                ->where("$card_num=?",$dataArr['card_number']) 
                ->where('card_pack_id=?',$dataArr['card_pack_id'])
                ->where('status="'.STATUS_FREE.'" OR status="'.STATUS_BLOCKED.'"  OR status="'.STATUS_USED.'"');

        $rs = $this->fetchRow($sql);
        if(!empty($rs)) {
            return TRUE;
        }
        if(isset($dataArr['member_id']) && !empty($dataArr['member_id'])) {
            $sql2 = $this->select()
                    ->where('member_id=?',$dataArr['member_id'])
                    ->where('product_id=?',$dataArr['product_id'])
                    ->where('status="'.STATUS_FREE.'" OR status="'.STATUS_BLOCKED.'" OR status="'.STATUS_USED.'"');

            $rs2 = $this->fetchRow($sql2);        
            if(!empty($rs2)) {
                return TRUE;
            }        
        }
        return FALSE;
    }
    
    public function checkDuplicateWithProductId($dataArr) {
        $crnKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$crnKey."') ");
        $sql = $this->select()
                ->where("$card_num=?",$dataArr['card_number'])
                ->where('card_pack_id=?',$dataArr['card_pack_id'])
                ->where('product_id=?',$dataArr['product_id'])                
                ->where('status="'.STATUS_FREE.'" OR status="'.STATUS_BLOCKED.'"  OR status="'.STATUS_USED.'"');

        $rs = $this->fetchRow($sql);
        if(!empty($rs)) {
            return TRUE;
        }
        if(isset($dataArr['member_id']) && !empty($dataArr['member_id'])) {
            $sql2 = $this->select()
                    ->where('member_id=?',$dataArr['member_id'])
                    ->where('product_id=?',$dataArr['product_id'])
                    ->where('status="'.STATUS_FREE.'" OR status="'.STATUS_BLOCKED.'" OR status="'.STATUS_USED.'"');

            $rs2 = $this->fetchRow($sql2);        
            if(!empty($rs2)) {
                return TRUE;
            }        
        }
        return FALSE;
    }
    
    
    public function getCrnInfobyMasterId($id) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $sql = $this->select()
                ->from($this->_name, array('id', 'product_id', $card_number, 'card_pack_id', 'member_id', 'date_expiry', 'status', 'file', 'by_ops_id', 'date_created'))
                ->where('id=?',$id);
        return $this->fetchRow($sql);
    }
    
    
    public function crnBulkUpdate($dataArr,$status) {
        if(!empty($dataArr)) {
            foreach ($dataArr as $id) {
                $info = $this->getCrnInfobyMasterId($id);
                if(!empty($info)) {
                    $upArr = array(
                        'status' => $status
                    );
                    $this->update($upArr, "id='".$id."'");
                }
            }
        }
    }
    
    
  public function getCRNInfo($cardNumber, $cardPackId='', $maId='') {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
       
        $sql = $this->select()
               ->from($this->_name, array('id', $card_number, 'card_pack_id', 'member_id'));
        if($cardNumber != '') { 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')"); 
            $sql->where('card_number=?',$cardNumber);
        }                        
        if($cardPackId != '') {
                $sql->where('card_pack_id=?',$cardPackId);
        }                        
        if($maId != '') {
                $sql->where('member_id=?',$maId);
        }                        
        $sql->where('status=?',STATUS_FREE);
        return $this->fetchRow($sql);
    }
    
    
  public function getInfoByCardNumberNPackId(array $param) {
  
        if(!isset($param['card_number'])) {
            throw new App_Exception('Invalid Card Number');
        } else {
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')");  
        }
        
        if(!isset($param['card_pack_id'])) {
            throw new App_Exception('Invalid Card Pack Id');
        }
        
        if(!isset($param['product_id'])) {
            throw new App_Exception('Invalid Product Id');
        }
      
        
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");  
        
        $sql = $this->select()
                ->from($this->_name, array('id', 'product_id', $card_number, 'card_pack_id', 'member_id', 'date_expiry', 'status', 'file', 'by_ops_id', 'date_created'))
                ->where('card_number=?',$param['card_number'])
                ->where('card_pack_id=?',$param['card_pack_id'])
                ->where('product_id=?',$param['product_id']);
        if(isset($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        //echo $sql;
        return $this->fetchRow($sql);
    }
    
    
    
    
  public function getInfoByMemberId(array $param) {
      
        if(!isset($param['member_id'])) {
            throw new App_Exception('Invalid Medi Assit Id');
        }
        
        if(!isset($param['product_id'])) {
            throw new App_Exception('Invalid Product Id');
        }
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number"); 
        $sql = $this->select()
                ->from($this->_name, array('id', 'product_id', $card_number, 'card_pack_id', 'member_id', 'date_expiry', 'status', 'file', 'by_ops_id', 'date_created'))
                ->where('member_id=?',$param['member_id'])
                ->where('product_id=?',$param['product_id']);
        if(isset($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        return $this->fetchRow($sql);
    }
    
  public function getInfoByMemberIdCardpackId(array $param) {
      
        if(!isset($param['member_id']) && !isset($param['card_pack_id'])) {
            throw new App_Exception('Invalid Member Id / Card Pack Id');
        }
        if($param['member_id'] == '' && $param['card_pack_id'] == '') {
            throw new App_Exception('Invalid Member Id / Card Pack Id');
        }
        
        if(!isset($param['product_id'])) {
            throw new App_Exception('Invalid Product Id');
        }
        
        //Decryption of Card Number
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cm`.`card_number`,'".$decryptionKey."') as card_number");
        
        $sql = $this->select();
        $sql->from(DbTable::TABLE_CRN_MASTER . ' as cm',array('id','product_id',$card_number,'card_pack_id','member_id','date_expiry','status','file','by_ops_id','date_created'));
        
        $sql->where('product_id=?',$param['product_id']);
        if(isset($param['member_id']) && $param['member_id'] != '') {
                $sql->where('member_id=?',$param['member_id']);
        }
        if(isset($param['card_pack_id']) && $param['card_pack_id'] != '') {
                $sql->where('card_pack_id=?',$param['card_pack_id']);
        }
        if(isset($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        return $this->fetchRow($sql);
    }
        
    
    
  public function updateStatusByCardNumberNPackId(array $param) {
        if(!isset($param['status'])) {
            throw new App_Exception ('CRN Master: Invalid status provided');
        }
        if(!isset($param['card_number'])) {
            throw new App_Exception ('CRN Master Update: Invalid Card Number provided');
        }
        $whereCon =array();
        if(isset($param['card_number'])) { 
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $param['card_number'] = new Zend_Db_Expr("AES_ENCRYPT('".$param['card_number']."','".$encryptionKey."')"); 
            $whereCon['card_number'] = $param['card_number'] ;  
        }
        if(isset($param['card_pack_id'])) { 
            $whereCon['card_pack_id'] = $param['card_pack_id'] ;
        }
        if(isset($param['product_id'])) {
            $whereCon['product_id'] = $param['product_id'] ;
        } 
        $dataArr = array(
            'status'  => $param['status']
        );
        return $this->update($dataArr, $whereCon);
   }
   
   
  public function updateStatusByMemberId(array $param) {
      
        if(!isset($param['status'])) {
            throw new App_Exception ('CRN Master: Invalid status provided');
        }               
        
        if(!isset($param['member_id'])) {
            throw new App_Exception ('CRN Master Update: Invalid Member ID provided');
        }               
        
        if(!isset($param['product_id'])) {
            throw new App_Exception ('CRN Master Update: Invalid Product ID provided');
        }               
        
        $whereCon = ' 1 ';
        if(isset($param['member_id'])) {
            $whereCon .= ' AND member_id="'.$param['member_id'].'" ';
        }
        
        if(isset($param['product_id'])) {
           $whereCon .= ' AND product_id="'.$param['product_id'].'" ';
        }
        
        $dataArr = array(
          'status'  => $param['status']
        );
        return $this->update($dataArr, $whereCon);


   }

   
  public function updateStatusByMemberIdCardpackId(array $param) {
      
        if(!isset($param['status'])) {
            throw new App_Exception ('CRN Master: Invalid status provided');
        }               
        
        if(!isset($param['member_id']) && !isset($param['card_pack_id'])) {
            throw new App_Exception ('CRN Master Update: Invalid Member ID / Card Pack Id provided');
        }  
        
        if($param['member_id'] == '' && $param['card_pack_id'] == '') {
            throw new App_Exception ('CRN Master Update: Invalid Member ID / Card Pack Id provided');
        }               
        
        if(!isset($param['product_id'])) {
            throw new App_Exception ('CRN Master Update: Invalid Product ID provided');
        }               
        
        $whereCon = ' 1 ';
        if(isset($param['member_id']) && $param['member_id'] != '') {
            $whereCon .= ' AND member_id="'.$param['member_id'].'" ';
        }
        if(isset($param['card_pack_id']) && $param['card_pack_id'] != '') {
            $whereCon .= ' AND card_pack_id="'.$param['card_pack_id'].'" ';
        }
        
        if(isset($param['product_id'])) {
           $whereCon .= ' AND product_id="'.$param['product_id'].'" ';
        }
        
        $dataArr = array(
          'status'  => $param['status']
        );
        return $this->update($dataArr, $whereCon);


   }
        
  public function searchCRNStatus(array $param,$type = 'SQL') {
        
        $cardNumber = isset($param['card_number']) ? $param['card_number'] : ''; 
        //Decryption of Card Number
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cm`.`card_number`,'".$decryptionKey."') as card_number");
        
        $sql = $this->select();
        $sql->from(DbTable::TABLE_CRN_MASTER . ' as cm',array('id','product_id',$card_number,'card_pack_id','member_id','date_expiry','status','file','by_ops_id','date_created'));
        $sql->where('product_id=?',$param['product_id']);
        if(!empty($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        if(!empty($cardNumber)) {
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $sql->where("card_number =?", $cardNumber);
            
        }
        if(!empty($param['file'])) {
                $sql->where('file=?',$param['file']);
        }
        if(!empty($param['card_pack_id'])) {
                $sql->where('card_pack_id=?',$param['card_pack_id']);
        }
        if($type == 'SQL') {
            return $sql;
        } 
        return $this->fetchAll($sql);
    }   
        
    public function fetchCRNforAPI(array $param,$type = 'SQL') {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cm`.`card_number`,'".$decryptionKey."') as card_number");
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') ");
        $sql = $this->select()
                ->from(DbTable::TABLE_CRN_MASTER . ' as cm',array('id','product_id',$card_number,'card_pack_id','member_id','date_expiry','status','file','by_ops_id','date_created'))
                ->where('product_id=?',$param['product_id']);
        if(!empty($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        if(!empty($param['card_number'])) {
                $sql->where("$card_num = ?",$param['card_number']);
        }
        if(!empty($param['file'])) {
                $sql->where('file=?',$param['file']);
        }
        if(!empty($param['card_pack_id'])) {
                $sql->where('card_pack_id=?',$param['card_pack_id']);
        }
        if($type == 'SQL') {
            return $sql;
        } 
        return $this->fetchRow($sql);
    }   
    
    
    public function getProductConst($cardNumber) {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') ");
        $sql = $this->select()
                ->where("$card_num = ?",$cardNumber)
                ->where('status=?',STATUS_USED);
        $rs = $this->fetchRow($sql);
        if(!empty($rs)) {
            $product = new Products();
            $productInfo = $product->getProductInfo($rs['product_id']);
            if(!empty($productInfo) && isset($productInfo['const'])) {
                return $productInfo['const'];
            }
        }
        return false;
    }
    
    public function getInfoByCardNumber(array $param) { 
        if(!isset($param['card_number'])) {
            throw new App_Exception('Invalid Card Number');
        }
        
        
        if(!isset($param['product_id'])) {
            throw new App_Exception('Invalid Product Id');
        }
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey; 
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') ");
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`cm`.`card_number`,'".$decryptionKey."') as card_number");
        $sql = $this->select()
                ->from(DbTable::TABLE_CRN_MASTER . ' as cm',array('id','product_id',$card_number,'card_pack_id','member_id','date_expiry','status','file','by_ops_id','date_created'))
                ->where(" $card_num = ?",$param['card_number'])
                ->where('product_id=?',$param['product_id']);
        if(isset($param['status'])) {
                $sql->where('status=?',$param['status']);
        }
        //echo $sql;
        return $this->fetchRow($sql);
    }
    
    public function updateStatusById(array $param,$id) {
      
        if(!isset($param['status'])) {
            throw new App_Exception ('CRN Master: Invalid status provided');
        }               
        
        $dataArr = array(
          'status'  => $param['status']
        );
        return $this->update($dataArr, "id = $id");


   }
   
   
    public function getCardExpiry(array $param) {
        
        if(!isset($param['card_number'])) {
            $this->setError('Invalid Card Number');
            return FALSE;
        }
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_num = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') ");
        
        $sql = $this->select();
        $sql->from(DbTable::TABLE_CRN_MASTER . ' as cm',array('date_expiry'));
         
        if(isset($param['card_number'])) {
            $sql->where("$card_num =?", $param['card_number']);
        }
        
        if(isset($param['status'])) {
            $sql->where('status=?',$param['status']);
        }
        if(isset($param['product_id'])) {
                $sql->where('product_id=?',$param['product_id']);
        }
        $rs = $this->fetchRow($sql);
        if(!empty($rs['date_expiry'])) {
            return $rs['date_expiry'];
        }
        return FALSE;
    }

    public function getCRNInfoByProduct($cardNumber, $cardPackId='', $productId='') {
        $decryptionKey = App_DI_Container::get('DbConfig')->crnkey;
        $card_number = new Zend_Db_Expr("AES_DECRYPT(`card_number`,'".$decryptionKey."') as card_number");
        $sql = $this->select(); 
        $sql->from(
                DbTable::TABLE_CRN_MASTER,
                array(
                    'id', 'product_id', $card_number, 'card_pack_id', 
                    'member_id', 'date_expiry', 'status', 'file', 
                    'by_ops_id', 'date_created'
        ));
        if($cardNumber != '') {
            $encryptionKey = App_DI_Container::get('DbConfig')->crnkey;
            $cardNumber = new Zend_Db_Expr("AES_ENCRYPT('".$cardNumber."','".$encryptionKey."')");
            $sql->where('card_number=?',$cardNumber);
        }                        
        if($cardPackId != '') {
                $sql->where('card_pack_id=?',$cardPackId);
        }                        
        if($productId != '') {
                $sql->where('product_id=?',$productId);
        }
        $sql->where('status=?',STATUS_FREE);
        return $this->fetchRow($sql);
    }    
    
    public function assignRatCorpCRN($cardHolderId = 0){
          
        if(!is_numeric($cardHolderId) || !$cardHolderId > 0) {
            throw new Exception("Invalid Cardholder id");
        }
        $product = new Products();
        $cardHolder = new Corp_Ratnakar_Cardholders();
        $row = $cardHolder->findById($cardHolderId);
        if (empty($row)) {
            throw new Exception("Cardholder not found!!");
        }
        //echo $row->product_id; exit;
        
        $crnArr = $this->fetchCRNforAPI(array('product_id'=>$row->product_id,'status'=>STATUS_FREE),'DATA');
        
        if (empty($crnArr)) {
            throw new Exception("CRN not found");
        }    
            
        //Assign CRN
        $updateArr = array(
            'crn' => $crnArr->card_number,
            'card_number' => $crnArr->card_number,
            'card_pack_id' => $crnArr->card_pack_id
        );
       
        $this->_db->update(DbTable::TABLE_RAT_CORP_CARDHOLDER, $updateArr, 'id="'.$cardHolderId.'"'); 
        
        $this->updateStatusByCardNumberNPackId(array(
            'card_number' => $crnArr->card_number,
            'card_pack_id' => $crnArr->card_pack_id,
            'product_id'     => $crnArr->product_id,
            'status'         => STATUS_USED
        ));
        
    }
}
