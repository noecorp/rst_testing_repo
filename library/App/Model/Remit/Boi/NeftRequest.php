<?php
/**
 * Remit Boi Neft Request Model
 * 
 *
 * @category App
 * @package App_Model
 * @copyright company
  */

class Remit_Boi_NeftRequest extends NeftRequest
{
    
    /*
     * downloads neft file containing associated records
     */
    public function downloadNeftTxt($fileName, $batchArr=array(), $createTxtFile=true, $downloadTxtFile=false, $filePermission=0444)
    {
        if(is_array($batchArr) && !empty($batchArr) && $createTxtFile){
            $batchRecords = $this->createBatchRecords($batchArr);
            $this->setStrBatch($batchRecords, SEPARATOR_PIPE);
            $this->setFilePermission($filePermission);
        }
        
            $this->setFilename($fileName);
            $this->setFilepath(UPLOAD_REMIT_BOI_PATH);
            $this->createTxtFile($createTxtFile, $downloadTxtFile);
            
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
        $i = 0;
        $batchRecords = array();
        foreach($batchArr as $arr)
        {
            $batchRecords[$i]['PAY_SYS_ID'] = TXN_NEFT;
            $batchRecords[$i]['CORP_ID'] = CORPORATE_ID_TSV;
            $batchRecords[$i]['TXN_AMT'] = floor($arr['amount']);
            $batchRecords[$i]['B_IFSC'] = $arr['ifsc_code'];
            $batchRecords[$i]['B_NAME'] = $arr['name'];
            $batchRecords[$i]['B_ACID'] = $arr['bank_account_number'];
            $batchRecords[$i]['B_ACID_TYP'] = Util::getAccountTypeNeft($arr['bank_account_type']);
            $batchRecords[$i]['B_ADDR'] = $arr['address_line1'];
            $batchRecords[$i]['B_EMAIL_ID'] = $arr['email'];
            $batchRecords[$i]['B_PHONE_NO'] = $arr['mobile'];
            $batchRecords[$i]['B_COMM_FLG'] = PREFFERED_COMM_CHANNEL;
            $batchRecords[$i]['TXN_RMKS'] = date("d").$arr['txn_code'];
            $batchRecords[$i]['RPT_CD'] = TXN_REPORT_CODE;
            $batchRecords[$i]['B_COMM'] = $arr['sender_msg'];
            $i++;
        }
        return $batchRecords;
        
    }
   
}