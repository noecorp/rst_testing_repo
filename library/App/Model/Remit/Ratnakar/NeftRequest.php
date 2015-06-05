<?php
/**
 * Remit Ratnakar Neft Request Model
 * 
 *
 * @category App
 * @package App_Model
 * @copyright company
  */

class Remit_Ratnakar_NeftRequest extends NeftRequest
{
    
    /*
     * downloads neft file containing associated records
     */
    public function downloadNeftTxt($fileName, $batchArr=array(), $createTxtFile=true, $downloadTxtFile=false, $filePermission=0755,$fileExt='')
    {
        if(is_array($batchArr) && !empty($batchArr) && $createTxtFile){
            $batchRecords = $this->createBatchRecords($batchArr);
            if($fileExt == FILE_CSV){
               $this->setStrBatch($batchRecords, SEPARATOR_COMMA);
            }elseif($fileExt == FILE_XLS){
               $this->setStrBatchXLS($batchRecords, SEPARATOR_COMMA);
            }else{
               $this->setStrBatch($batchRecords, SEPARATOR_PIPE);
            }
            $this->setFilePermission($filePermission);
        }
        
            $this->setFilename($fileName,$fileExt);
            $this->setFilepath(UPLOAD_REMIT_RAT_PATH);
            if($fileExt == FILE_XLS){
            $this->createXlSFile($createTxtFile, $downloadTxtFile);
            }else{
            $this->createTxtFile($createTxtFile, $downloadTxtFile);
            }
            
    }
    
  
    
    /*
     * function to create batch records for BOI Neft processing
     * array elements as follows:
        PAY_SYS_ID - The Indentifier to differentiate NEFT and RTGS transaction = default NEFT
        CORP ID - Corp Id of the user = default	TRANSERVPVTLTD -> updated to TRANSERVPRIVATELTD
        TXN_AMT - Transaction Amount without decimals = Eg.: 250000 (Rs. 2500) -> updated to 2500 only
        B_IFSC - Beneficiary IFSC	
        B_NAME - Beneficiary Name	
        B_ACID - Beneficiary Account Number	
        B_ACID_TYP - Beneficiary Account Type = "10 for Saving, 11 for Current"
        B_ADDR - Beneficiary Address line 1
        B_EMAIL_ID - Beneficiary Email Id	
        B_PHONE_NO - Beneficiary Mobile Phone Number	
        B_COMM_FLG - The flag indicating the preffered communication channel = "M for Mobile, E for Email"
        TXN_RMKS - Transerv txn ref no
        RPT_CD - Transaction Report Code = default 90909
        B_COMM - Sender to Beneficiary communication
     */
    public function createBatchRecords($batchArr)
    {
            $batchRecords = array();
            $batchRecords[0]['PAYMENT_TYPE'] = 'Payment Type';
            $batchRecords[0]['CUST_REF_NUM'] = 'Cust Ref Number';
            $batchRecords[0]['S_ACT_NO'] = 'Source Account Number';
            $batchRecords[0]['S_NARRATION'] = 'Source Narration';
            $batchRecords[0]['D_ACT_NO'] = 'Destination Account Number';
            $batchRecords[0]['CURENCY'] = 'Currency';
            $batchRecords[0]['AMOUNT'] = 'Amount';
            $batchRecords[0]['D_NARRATION'] = 'Destination Narration';
            $batchRecords[0]['D_BANK'] = 'Destination bank';
            $batchRecords[0]['D_BANK_IFSC'] = 'Destination Bank IFS Code';
            $batchRecords[0]['BENE_NAME'] = 'Beneficiary Name';
            $batchRecords[0]['BENE_ACT_TYPE'] = 'Beneficiary Account Type';
            $batchRecords[0]['SENDER_TO_RECEIVER1'] = 'Sender to Receiver 1';
            $batchRecords[0]['SENDER_TO_RECEIVER2'] = 'Sender to Receiver 2';
            $batchRecords[0]['SENDER_TO_RECEIVER3'] = 'Sender to Receiver 3';
            $batchRecords[0]['SENDER_TO_RECEIVER4'] = 'Sender to Receiver 4';
            $batchRecords[0]['SENDER_TO_RECEIVER5'] = 'Sender to Receiver 5';
            $batchRecords[0]['SENDER_TO_RECEIVER6'] = 'Sender to Receiver 6';
            $batchRecords[0]['CHEQUE_NO'] = 'Cheque Number';
            $batchRecords[0]['SIG_CODE1'] = 'Signatory Code 1';
            $batchRecords[0]['SIG_CODE2'] = 'Signatory Code 2';
            $batchRecords[0]['PRINT_LOC'] = 'Print Location';
            $batchRecords[0]['DRAWN_ON_LOC'] = 'Drawn on Location';
            $batchRecords[0]['PAYEE_NAME'] = 'Payee Name';
            $batchRecords[0]['CORR_BANK'] = 'Corr Bank';
        $i = 1;
       
        foreach($batchArr as $arr)
        {
            $batchRecords[$i]['PAYMENT_TYPE'] = RAT_NEFT_PAYMENT_TYPE;
            $batchRecords[$i]['CUST_REF_NUM'] = $arr['txn_code'];
            $batchRecords[$i]['S_ACT_NO'] = S_ACT_NO;
            $batchRecords[$i]['S_NARRATION'] = NEFT_SENDER_NARRATION;
            $batchRecords[$i]['D_ACT_NO'] = $arr['bank_account_number'];
            $batchRecords[$i]['CURENCY'] = CURRENCY_INR;
            $batchRecords[$i]['AMOUNT'] = $arr['amount'];
            $batchRecords[$i]['D_NARRATION'] = 'RBL SHMART REMITTACNE TRXN';
            $batchRecords[$i]['D_BANK'] = $arr['bene_bank_name'];
            $batchRecords[$i]['D_BANK_IFSC'] = $arr['ifsc_code'];
            $batchRecords[$i]['BENE_NAME'] = $arr['beneficiary_name'];
            $batchRecords[$i]['BENE_ACT_TYPE'] = $arr['bank_account_type'];
            $batchRecords[$i]['SENDER_TO_RECEIVER1'] = '';
            $batchRecords[$i]['SENDER_TO_RECEIVER2'] = '';
            $batchRecords[$i]['SENDER_TO_RECEIVER3'] = '';
            $batchRecords[$i]['SENDER_TO_RECEIVER4'] = '';
            $batchRecords[$i]['SENDER_TO_RECEIVER5'] = '';
            $batchRecords[$i]['SENDER_TO_RECEIVER6'] = '';
            $batchRecords[$i]['CHEQUE_NO'] = '';
            $batchRecords[$i]['SIG_CODE1'] = '';
            $batchRecords[$i]['SIG_CODE2'] = '';
            $batchRecords[$i]['PRINT_LOC'] = '';
            $batchRecords[$i]['DRAWN_ON_LOC'] = '';
            $batchRecords[$i]['PAYEE_NAME'] = '';
            $batchRecords[$i]['CORR_BANK'] = '';
            $i++;
        }
        return $batchRecords;


    }
    
    
     public function downloadUnsettlementTxt($fileName, $batchArr=array(), $createTxtFile=true, $downloadTxtFile=false, $filePermission=0755,$fileExt='')
    {
        if(is_array($batchArr) && !empty($batchArr) && $createTxtFile){
            $batchRecords = $this->createUnsettlementBatchRecords($batchArr);
            if($fileExt == FILE_CSV){
               $this->setStrBatch($batchRecords, SEPARATOR_COMMA);
            }elseif($fileExt == FILE_XLS){
             $this->setStrBatchXLS($batchRecords, SEPARATOR_COMMA);
            }else{
               $this->setStrBatch($batchRecords, SEPARATOR_PIPE);
            }
            $this->setFilePermission($filePermission);
        }
        
            $this->setFilename($fileName,$fileExt);
            $this->setFilepath(UPLOAD_CUSTOMER_RATNAKAR_SETTLEMENT);
            if($fileExt == FILE_XLS){
            $this->createXlSFile($createTxtFile, $downloadTxtFile);
            }else{
            $this->createTxtFile($createTxtFile, $downloadTxtFile);
            }
            
    }
    
    public function createUnsettlementBatchRecords($batchArr)
    {
            $batchRecords = array();
            $batchRecords[0]['ORDERING_ACC_NO'] = 'ORDERING ACC NO';
            $batchRecords[0]['REMITTER_NAME'] = 'REMITTER NAME';
            $batchRecords[0]['IFSCCODE'] = 'IFSC CODE';
            $batchRecords[0]['BENEACCNO'] = 'BENE ACC NO';
            $batchRecords[0]['BENENAME'] = 'BENE NAME';
            $batchRecords[0]['BENECITY'] = 'BENE ADDRESS';
            $batchRecords[0]['TXNREFNO'] = 'TXN REF NO';
            $batchRecords[0]['DATE'] = 'DATE';
            $batchRecords[0]['NET_PAYMENT'] = 'NET PAYMENT';
            $batchRecords[0]['SENT_TO_RECV_INFO'] = 'SENT TO RECV INFO';
            $batchRecords[0]['INDICATOR'] = 'INDICATOR';
            $batchRecords[0]['AGENT_EMAIL'] = 'DETAIL';
            $batchRecords[0]['ORIGINAL_REMITTER'] = 'ORIGINAL REMITTER';
            
        $i = 1;
       
        foreach($batchArr as $key=>$arr)
        {
            if( ((string)$key !='settlement_req_id') && ((string)$key !='batch_name') ){
            $amount = Util::numberFormat($arr['amount'],FLAG_NO);    
            $date = date('d-m-y',  time());
            $batchRecords[$i]['ORDERING_ACC_NO'] = RAT_ORDERING_ACC_NO;
            $batchRecords[$i]['REMITTER_NAME'] = COMPANY_NAME;
            $batchRecords[$i]['IFSCCODE'] = $arr['agent_ifsc_code'];
            $batchRecords[$i]['BENEACCNO'] = $arr['agent_account_number'];
            $batchRecords[$i]['BENENAME'] = $arr['agent_name'];
            $batchRecords[$i]['BENECITY'] = $arr['city'];
            $batchRecords[$i]['TXNREFNO'] = $arr['txn_code'];
            $batchRecords[$i]['DATE'] = $date;
            $batchRecords[$i]['NET_PAYMENT'] = $amount;
            $batchRecords[$i]['SENT_TO_RECV_INFO'] = SETTLEMENT_FROM;
            $batchRecords[$i]['INDICATOR'] = SETTLEMENT_INDICATOR;
            $batchRecords[$i]['AGENT_EMAIL'] = $arr['email'];
            $batchRecords[$i]['SETTLEMENT_REMITTER'] = SETTLEMENT_REMITTER;
            
            $i++;
            }
        }
        return $batchRecords;


    }
   
}