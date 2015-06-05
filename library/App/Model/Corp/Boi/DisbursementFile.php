<?php

/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class Corp_Boi_DisbursementFile extends Corp_Boi {

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
    protected $_name = DbTable::TABLE_BOI_DISBURSEMENT_FILE;

    protected $_bucket='';

    public function generateBOIDisbursementFile()
    {

        try {
            
            $this->_db->beginTransaction();
            
        $cardLoad = new Corp_Boi_Cardload();
        
        $this->updatePendingBucket();
        $this->updateWrongAmountBucket();

        
        $disRecords = $this->getDisbursemtentNumberForTTUMFile();
        $count = 0;
        $masterFileName ='';
        if(!empty($disRecords)) {
            //echo '<pre>';print_r($disRecords);exit;
            foreach ($disRecords as $value) { 
                $ttumRecords = $this->getMatchedRecordsForByDisbursementNumber($value['disbursement_number']);
                if(!empty($ttumRecords)) {
                    $count = count($ttumRecords)-1;// exit;
                    $tottalFiles =  ceil($count/CORP_DISBURESEMENT_TITUM_MAX_RECORDS);
                    $newTtumRecords = Util::fill_chunck($ttumRecords,$tottalFiles);
                    //Util::debug($newTtumRecords);
                    for($i=1;$i<=$tottalFiles;$i++){
                        
                        $fileName = $this->generateFileNameForDisbursement($value['disbursement_number'],'ttum',count($newTtumRecords[$i]),$i);
                        $cardLoad->generateTTUMFileForDisbursement($value['disbursement_number'], $newTtumRecords[$i], array('date' => date('Y-m-d'),'file_name' => $fileName));
                        
                        //$walletFileName = $this->generateFileNameForDisbursement($value['disbursement_number'],'wallet');                                        
                        //$cardLoad->generateWalletFileForDisbursement($ttumRecords, array('date' => date('Y-m-d'),'file_name' => $walletFileName));
                        
                        $fileId = $this->addTTUMFile($fileName, $value['disbursement_number']);
                        $this->markDisbursementRecordActive($fileId,$value['disbursement_number']);
                        $masterFileName = $masterFileName . '  ' . $fileName;
                        $count++;
                    }   
                }
            }
        }
        $this->_db->commit();
        return array('count' => $count, 'file' => $masterFileName);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage());
            $this->_db->rollBack();
            return array('error_msg' => $e->getMessage());
        }
    }
    
    
    private function markDisbursementRecordActive($fileId,$disNo) {
            
        $this->_db->update(DbTable::TABLE_BOI_DISBURSEMENT_BATCH, array(
                'ttum_file_id' => $fileId
            ), 'bucket="'.BUCKET_TYPE_MATCHED.'" AND disbursement_number="'.$disNo.'" AND (ISNULL(ttum_file_id) OR ttum_file_id ="") ');        
        
    }
    
    public function getPendingDisbursementRecords() {
           $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_DISBURSEMENT_BATCH)
                   ->where('payment_status=?',STATUS_PENDING)
                   ->where('wallet_status=?',STATUS_PENDING)
                   ->where('status = ? ', STATUS_ACTIVE)
                   ->where('ISNULL(bucket) ')
                   ->orWhere('bucket =""')
                   ->orWhere('bucket=?','0')
                   //->orWhere('bucket=?',BUCKET_TYPE_MATCH_AADHAAR)
                   //->orWhere('bucket=?',BUCKET_TYPE_MATCH_ACCOUNT)
                   //->orWhere('bucket=?',BUCKET_TYPE_NOT_MATCHED)
                   ////->orWhere('bucket=?',BUCKET_TYPE_WRONG_AMOUNT)
                   ->order("id DESC");
        $dataArray = $this->_db->fetchAll($select);
        return $dataArray;  
    }
    
    
    public function getPendingDisbursementRecordsForAadhar() {
           $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_DISBURSEMENT_BATCH)
                   ->where('status = ? ', STATUS_ACTIVE)
                   ->where('ISNULL(bucket) ')
                   ->orWhere('bucket =""')
                   ->orWhere('bucket=?','0')
                   //->orWhere('bucket=?',BUCKET_TYPE_MATCH_AADHAAR)
                   //->orWhere('bucket=?',BUCKET_TYPE_MATCH_ACCOUNT)
                   ->orWhere('bucket=?',BUCKET_TYPE_NOT_MATCHED)
                   ->order("id DESC");
        $dataArray = $this->_db->fetchAll($select);
        return $dataArray;  
    }
    
    private function updateBucket($batchId, $bucket)
    {
            $this->_db->update(DbTable::TABLE_BOI_DISBURSEMENT_BATCH, array(
                'bucket' => $bucket,
            ), 'id="'.$batchId.'"');        
    }
    
    private function updateBucketForWrongDebitMandateAmount($batchId, $bucket)
    {
            $this->_db->update(DbTable::TABLE_BOI_DISBURSEMENT_BATCH, array(
                'bucket' => $bucket,
                  'payment_status' => STATUS_PENDING,
            ), 'id="'.$batchId.'"');        
    }
    
    
    public function updatePendingBucket() {
        $dataArray = $this->getPendingDisbursementRecords();
        foreach ($dataArray as $value) {
            $response = $this->matchDisbursementDetails(array(
                'aadhaar_no' => $value['aadhar_no'],
                'account_no' => $value['account_number'],
            ));
            
            $updateArr = array(
                        'customer_master_id' => $response['customer_master_id'],
                        'product_customer_id' => $response['id']
                    );
            
            $bucket = $this->getBucket();
            if($bucket == 1){
                $updateArr['payment_status'] = STATUS_GENERATED;
            }
            
            $this->updateDisbBatchDetails($updateArr,$value['id']);
            
            $this->updateBucket($value['id'], $bucket);
        }
        //END;
   }
   
   
   public function updateWrongAmountBucket() {
        $dataArray = $this->getRecordsForByWrongAmount();
        $bucket = '9';
        foreach ($dataArray as $value) {
           $this->updateBucketForWrongDebitMandateAmount($value['id'], $bucket);
        }
   }
   
   public function correctWrongAmountBucket() {
        $dataArray = $this->getRecordsForByWrongAmount();
        $bucket = '9';
        foreach ($dataArray as $value) {
           $this->updateBucketForWrongDebitMandateAmount($value['id'], $bucket);
        }
   }
    
    public function updatePendingBucketForAadhar() {
        $dataArray = $this->getPendingDisbursementRecordsForAadhar();
        foreach ($dataArray as $value) {
            
            $response = $this->matchDisbursementAadhar(array(
                'aadhaar_no' => $value['aadhar_no'],
                'account_no' => $value['account_number'],
            ));
          
            if(empty($response)) {
                $bucket = '4'; //Not Matched
            } else {
                if ($response['aadhaar_no'] == $value['aadhar_no'] ) {
                    $bucket = '2'; //2.	Aadhaar Match – Account Not Matched
                } else { // Handling Exception
                    $bucket = '4'; //Not Matched                    
                }
            }
            $this->updateBucket($value['id'], $bucket);
        }
        //END;
   }
   
   private function matchDisbursementAccount($param) {
        $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER)
                   ->where('status="'.STATUS_ACTIVE.'" OR status="'.STATUS_ACTIVATED.'"')
                   ->where('aadhaar_no=?',$param['aadhaar_no'])
                   ->where('account_no=?',$param['account_no']);
        $rs =  $this->_db->fetchRow($select);
        if(empty($rs)) {
                 $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER)
                   ->where('(status="'.STATUS_ACTIVE.'" OR status="'.STATUS_ACTIVATED.'") AND (aadhaar_no="'.$param['aadhaar_no'].'" OR account_no="'.$param['account_no'].'")');
                   //->where('aadhaar_no=?',$param['aadhaar_no'])
                   //->orWhere('account_no=?',$param['account_no']);
                 //echo $select;exit;
           $rs = $this->_db->fetchRow($select);
        }
        return $rs;
   }
   
   private function matchDisbursementAadhar($param) {
        $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER)
                   //->where('status="'.STATUS_ACTIVE.'" OR status="'.STATUS_ACTIVATED.'"')
                   ->where('aadhaar_no=?',$param['aadhaar_no']);
                   //->orWhere('account_no=?',$param['account_no']);
        return $this->_db->fetchRow($select);
   }
   
   private function getMatchedRecordsForByDisbursementNumber($disNumber)
   {
           $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_DISBURSEMENT_BATCH ." as b",array('b.id', 'b.product_id', 'b.customer_master_id', 'b.product_customer_id', 'b.txn_identifier', 'b.account_number', 'b.ifsc_code', 'b.aadhar_no', 'b.amount', 'b.currency', 'b.narration', 'b.wallet_code', 'b.txn_no', 'b.card_type', 'b.corporate_id', 'b.mode', 'b.bucket', 'b.status', 'b.disbursement_number', 'b.batch_name', 'b.ttum_file_id', 'b.date_updated', 'b.date_create', 'b.failed_reason'))
                   ->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER ." as c","b.product_customer_id = c.id",array('c.debit_mandate_amount','c.ref_num'))
                   ->where('b.bucket=?',BUCKET_TYPE_MATCHED)
                   ->where('b.disbursement_number=?',$disNumber)
                   ->where('ISNULL(b.ttum_file_id) OR b.ttum_file_id=""')
                   //->group('b.account_number')
                   ->order("b.id ASC");
                   //echo $select; exit;
        $dataArray = $this->_db->fetchAll($select);
        return $dataArray;  
   }
   
   
   private function getRecordsForByWrongAmount()
   {
           $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_DISBURSEMENT_BATCH ." as b",array('b.id', 'b.product_id', 'b.customer_master_id', 'b.product_customer_id', 'b.txn_identifier', 'b.account_number', 'b.ifsc_code', 'b.aadhar_no', 'b.amount', 'b.currency', 'b.narration', 'b.wallet_code', 'b.txn_no', 'b.card_type', 'b.corporate_id', 'b.mode', 'b.bucket', 'b.status', 'b.disbursement_number', 'b.batch_name', 'b.ttum_file_id', 'b.date_updated', 'b.date_create', 'b.failed_reason'))
                   ->joinLeft(DbTable::TABLE_BOI_CORP_CARDHOLDER ." as c","b.product_customer_id = c.id",array('c.debit_mandate_amount'))
                   ->where('b.bucket=?',BUCKET_TYPE_MATCHED)
                   //->where('b.disbursement_number=?',$disNumber)
                   ->where('ISNULL(b.ttum_file_id) OR b.ttum_file_id=""')
                   ->where('c.debit_mandate_amount > b.amount')
                   ->order("b.id ASC");
        $dataArray = $this->_db->fetchAll($select);
        return $dataArray;  
   }
   
   private function getDisbursemtentNumberForTTUMFile()
   {
           $select = $this->_db->select()
                   ->from(DbTable::TABLE_BOI_DISBURSEMENT_BATCH)
                   ->where('bucket=?',BUCKET_TYPE_MATCHED)
                   ->where('status=?',STATUS_ACTIVE)
                   ->group('disbursement_number');
                   //echo $select; exit;
        return $this->_db->fetchAll($select);
   }
   
   
   private function addTTUMFile($fileName,$disNo) {
       $this->_db->insert(DbTable::TABLE_BOI_DISBURSEMENT_FILE, array(
           'file_name'  => $fileName,
           //'wallet_file_name'  => $walletFileName,
           'status'     => STATUS_ACTIVE,
           'disbursement_no'=>$disNo,
           'date_updated'=> new Zend_Db_Expr('NOW()'),
           'date_created'=> new Zend_Db_Expr('NOW()')
       ));
       return $this->_db->lastInsertId(DbTable::TABLE_BOI_DISBURSEMENT_FILE);
   }
   
   private function generateFileNameForDisbursement($disNum,$type='ttum',$cnt=0,$part=0) {
       if($type=='ttum') {
        $ext = '.txt';
        if($part){
            $part = sprintf('%02s', $part);  
            return 'TTUM_'.date('dmyhis').'_'.$disNum.'_'.$cnt.'_'.$part.$ext;
        }else{
            return 'TTUM_'.date('dmyhis').'_'.$disNum.'_'.$cnt.$ext;
        }
       } elseif($type=='wallet') {
        $ext = '.csv';
        return 'BUPNSDC_'.$disNum.'_'.date('dmY').$ext;
       }
       
   }

   public function updateDisbBatchDetails($updateArr ,$id){
       return $this->_db->update(DbTable::TABLE_BOI_DISBURSEMENT_BATCH,$updateArr, "id = $id");        
        
   }
   
  /* 
    public function loadProcessedAccounts()
    {
        try {
        $cardLoad = new Corp_Boi_Cardload();
        
        $disRecords = $this->getPendingAccountForLoad();
        $count = 0;
        if(!empty($disRecords)) {
            foreach ($disRecords as $value) { 
                $ttumRecords = $this->getMatchedRecordsForByDisbursementNumber($value['disbursement_number']);
                if(!empty($ttumRecords)) {
                    $fileName = $this->generateFileNameForDisbursement($value['disbursement_number'],'ttum',count($ttumRecords));
                    $cardLoad->generateTTUMFileForDisbursement($value['disbursement_number'], $ttumRecords, array('date' => date('Y-m-d'),'file_name' => $fileName));
                    
                    //$walletFileName = $this->generateFileNameForDisbursement($value['disbursement_number'],'wallet');                                        
                    //$cardLoad->generateWalletFileForDisbursement($ttumRecords, array('date' => date('Y-m-d'),'file_name' => $walletFileName));
                    
                    $fileId = $this->addTTUMFile($fileName, $value['disbursement_number'],$walletFileName);
                    $this->markDisbursementRecordActive($fileId,$value['disbursement_number']);
                    $masterFileName = $masterFileName . '  ' . $fileName;
                    $count++;
                }
            }
        }
        return array('count' => $count, 'file' => $masterFileName);
        } catch (Exception $e) {
            App_Logger::log($e->getMessage());
            return array('error_msg' => $e->getMessage());
        }
    }
*/
 private function matchDisbursementDetails($param) {
        $select = $this->_db->select()
                ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER,array('id','customer_master_id'))
                ->where('status="' . STATUS_ACTIVE . '" OR status="' . STATUS_ACTIVATED . '"')
                ->where('aadhaar_no = ?', $param['aadhaar_no'])
                ->where('account_no = ?', $param['account_no']);
        $rs = $this->_db->fetchRow($select);
        if (empty($rs)) {
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER,array('id','customer_master_id'))
                    ->where('(status="' . STATUS_ACTIVE . '" OR status="' . STATUS_ACTIVATED . '" OR status="' . STATUS_PENDING . '") AND (aadhaar_no != "' . $param['aadhaar_no'] . '" AND account_no = "' . $param['account_no'] . '")');
            $rsAC = $this->_db->fetchRow($select);
            if (!empty($rsAC)) {
                $this->setBucket(3);//Updating Bucket No
                return $rsAC;
            } else {
                $select = $this->_db->select()
                        ->from(DbTable::TABLE_BOI_CORP_CARDHOLDER,array('id','customer_master_id'))
                        ->where('(status="' . STATUS_ACTIVE . '" OR status="' . STATUS_ACTIVATED . '" OR status="' . STATUS_PENDING . '") AND (aadhaar_no = "' . $param['aadhaar_no'] . '" AND account_no != "' . $param['account_no'] . '")');
                $rsAN = $this->_db->fetchRow($select);
                if (!empty($rsAN)) {
                    $this->setBucket(2);;//Updating Bucket No
                    return $rsAN;
                }
            }
            $this->setBucket(4);
            return array();
        } else {
            $resp = $this->ifMatchedBucketExists($rs);
            if($resp == true){
                $this->setBucket(1);
            } else {
                $this->setBucket(8);
            }
            return $rs;
        }
    }

    private function setBucket($bucket)
   {
       $this->_bucket = $bucket;
   }
   
   private function getBucket()
   {
       return $this->_bucket;
   }
  
    private function ifMatchedBucketExists($param) {

        $sql = $this->_db->select()
                ->from(DbTable::TABLE_BOI_DISBURSEMENT_BATCH, array('id'))
                ->where('customer_master_id = ?', $param['customer_master_id'])
                ->where('product_customer_id = ?', $param['id'])
                ->where('status = ?', STATUS_ACTIVE)
                ->where('payment_status="'.STATUS_PROCESSED.'" OR payment_status="'.STATUS_GENERATED.'"')
                ->where('bucket=?', 1);
        $rs = $this->_db->fetchRow($sql);

        if(!empty($rs)) {
            return false;
        } else {
            return true;
        }
    }    
}
