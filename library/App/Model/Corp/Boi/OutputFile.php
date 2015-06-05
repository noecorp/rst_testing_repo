<?php

/**
 * Corp Boi OutputFile Model
 * 
 *
 * @category App
 * @package App_Model
 * @copyright company
  */

class Corp_Boi_OutputFile extends OutputFile
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
    protected $_name = DbTable::TABLE_BOI_OUTPUT_FILE;

    /*
     * downloads neft file containing associated records
     */
    public function downloadTxt($fileName, $batchArr=array(), $createTxtFile=true, $downloadTxtFile=false, $filePermission=0444)
    {
        if(is_array($batchArr) && !empty($batchArr) && $createTxtFile){
            $batchRecords = $this->createBatchRecords($batchArr);
            $this->setStrBatch($batchRecords, SEPARATOR_PIPE);
            $this->setFilePermission($filePermission);
        }
        
            $this->setFilename($fileName);
            $this->setFilepath(UPLOAD_BOI_NSDC_PATH);
            $this->createTxtFile($createTxtFile, $downloadTxtFile);
            
    }
    
    /*
     * function to create batch records 
     * array elements as follows:
        SYS_ID|SOL_ID|FIRST_HOLDER_SALUTATION|FIRST_HOLDER_NAME|FIRST_HOLDER_OCCUPATION|FIRST_HOLDER_GENDER|FIRST_HOLDER_DOB|ADDRESS_TYPE|FIRST_HOLDER_PERM_ADD_1|FIRST_HOLDER_PERM_ADD_2|FIRST_HOLDER_PERM_CITY|FIRST_HOLDER_PERM_STATE|FIRST_HOLDER_PERM_COUNTRY|FIRST_HOLDER_PERM_PIN|FIRST_HOLDER_COMM_ADD_1|FIRST_HOLDER_COMM_ADD_2|FIRST_HOLDER_COMM_CITY|FIRST_HOLDER_COMM_STATE|FIRST_HOLDER_COMM_COUNTRY|FIRST_HOLDER_COMM_PIN|PHONE_NUM|MOBILE_NUM|EMAIL_ADD|PAN|UID_NUM|NRE_FLG|NRE_NATIONALITY|PASSPORT_NUM|PASSPORT_ISSUE_DT|PASSPORT_EXPIRY_DT|MARITAL_STATUS|CUST_COMM_CODE|OTHER_BANK_AC_NUM|OTHER_BANK_AC_TYPE|OTHER_BANK_NAME|OTHER_BANK_BRANCH|EMPLOYER_NAME|EMPLOYER_ADD1|EMPLOYER_ADD2|EMPLOYER_CITY|EMPLOYER_STATE|EMPLOYER_COUNTRY|EMPLOYER_PIN|EMPLOYER_CONTACT_NUM|MINOR_FLG|MINOR_GUARDIAN_CODE|MINOR_GUARDIAN_NAME|MINOR_GUARDIAN_ADD1|MINOR_GUARDIAN_ADD2|MINOR_GUARDIAN_CITY|MINOR_GUARDIAN_STATE|MINOR_GUARDIAN_PIN|MINOR_GUARDIAN_COUNTRY|MODE_OF_OPERATION|NOMINATION_FLG|NOMINEE_NAME|NOMINEE_ADD1|NOMINEE_ADD2|NOMINEE_RELATION_CD|NOMINEE_CITY_CD|NOMINEE_MINOR_GUARDIAN_CD|NOMINEE_DOB|NOMINEE_MINOR_FLAG|AMOUNT_OPEN|MODE_OF_PAYMENT_OPEN|ACCOUNT_NUMBER|CUST_ID|SQLID|FINACLE_STATUS|UPDATESQL_STATUS|STAFF_FLG|STAFF_NO|MINOR_GUARDIAN_TITLE_CODE|PASSPORT_DETAILS|INTRODUCER_TITLE_CODE|INTRODUCER_NAME|EXISTING_CUST_FLG|ACCT_CURRENCY_CODE|CUST_ID_VER_FLG|ACCT_ID_VER_FLG|SCHM_CODE|ORGANISATION_TYPE|INTRODUCER_FLG|INTRODUCER_CUST_ID|CUST_CURRENCY_CODE|CREATED_DATE|ACCOUNT_TYPE_ID|REF_NUM|DEBIT_MANDATE
     */
    public function createBatchRecords($batchArr)
    {
        $i = 0;
        $addLen = 45;
        $pinLen = 6;
        $batchRecords = array();
        $rctModel = new RctMaster();
//        $batchRecords[$i]['SYS_ID'] = 'SYS_ID';
//        $batchRecords[$i]['SOL_ID'] = 'SOL_ID';
//        $batchRecords[$i]['FIRST_HOLDER_SALUTATION'] = 'FIRST_HOLDER_SALUTATION';
//        $batchRecords[$i]['FIRST_HOLDER_NAME'] = 'FIRST_HOLDER_NAME';
//        $batchRecords[$i]['FIRST_HOLDER_OCCUPATION'] = 'FIRST_HOLDER_OCCUPATION';
//        $batchRecords[$i]['FIRST_HOLDER_GENDER'] = 'FIRST_HOLDER_GENDER';
//        $batchRecords[$i]['FIRST_HOLDER_DOB'] = 'FIRST_HOLDER_DOB';
//        $batchRecords[$i]['ADDRESS_TYPE'] = 'ADDRESS_TYPE';
//        $batchRecords[$i]['FIRST_HOLDER_PERM_ADD_1'] = 'FIRST_HOLDER_PERM_ADD_1';
//        $batchRecords[$i]['FIRST_HOLDER_PERM_ADD_2'] = 'FIRST_HOLDER_PERM_ADD_2';
//        $batchRecords[$i]['FIRST_HOLDER_PERM_CITY'] = 'FIRST_HOLDER_PERM_CITY';
//        $batchRecords[$i]['FIRST_HOLDER_PERM_STATE'] = 'FIRST_HOLDER_PERM_STATE';
//        $batchRecords[$i]['FIRST_HOLDER_PERM_COUNTRY'] = 'FIRST_HOLDER_PERM_COUNTRY';
//        $batchRecords[$i]['FIRST_HOLDER_PERM_PIN'] = 'FIRST_HOLDER_PERM_PIN';
//        $batchRecords[$i]['FIRST_HOLDER_COMM_ADD_1'] = 'FIRST_HOLDER_COMM_ADD_1';
//        $batchRecords[$i]['FIRST_HOLDER_COMM_ADD_2'] = 'FIRST_HOLDER_COMM_ADD_2';
//        $batchRecords[$i]['FIRST_HOLDER_COMM_CITY'] = 'FIRST_HOLDER_COMM_CITY';
//        $batchRecords[$i]['FIRST_HOLDER_COMM_STATE'] = 'FIRST_HOLDER_COMM_STATE';
//        $batchRecords[$i]['FIRST_HOLDER_COMM_COUNTRY'] = 'FIRST_HOLDER_COMM_COUNTRY';
//        $batchRecords[$i]['FIRST_HOLDER_COMM_PIN'] = 'FIRST_HOLDER_COMM_PIN';
//        $batchRecords[$i]['PHONE_NUM'] = 'PHONE_NUM';
//        $batchRecords[$i]['MOBILE_NUM'] = 'MOBILE_NUM';
//        $batchRecords[$i]['EMAIL_ADD'] = 'EMAIL_ADD';
//        $batchRecords[$i]['PAN'] = 'PAN';
//        $batchRecords[$i]['UID_NUM'] = 'UID_NUM';
//        $batchRecords[$i]['NRE_FLG'] = 'NRE_FLG';
//        $batchRecords[$i]['NRE_NATIONALITY'] = 'NRE_NATIONALITY';
//        $batchRecords[$i]['PASSPORT_NUM'] = 'PASSPORT_NUM';
//        $batchRecords[$i]['PASSPORT_ISSUE_DT'] = 'PASSPORT_ISSUE_DT';
//        $batchRecords[$i]['PASSPORT_EXPIRY_DT'] = 'PASSPORT_EXPIRY_DT';
//        $batchRecords[$i]['MARITAL_STATUS'] = 'MARITAL_STATUS';
//        $batchRecords[$i]['CUST_COMM_CODE'] = 'CUST_COMM_CODE';
//        $batchRecords[$i]['OTHER_BANK_AC_NUM'] = 'OTHER_BANK_AC_NUM';
//        $batchRecords[$i]['OTHER_BANK_AC_TYPE'] = 'OTHER_BANK_AC_TYPE';
//        $batchRecords[$i]['OTHER_BANK_NAME'] = 'OTHER_BANK_NAME';
//        $batchRecords[$i]['OTHER_BANK_BRANCH'] = 'OTHER_BANK_BRANCH';
//        $batchRecords[$i]['EMPLOYER_NAME'] = 'EMPLOYER_NAME';
//        $batchRecords[$i]['EMPLOYER_ADD1'] = 'EMPLOYER_ADD1';
//        $batchRecords[$i]['EMPLOYER_ADD2'] = 'EMPLOYER_ADD2';
//        $batchRecords[$i]['EMPLOYER_CITY'] = 'EMPLOYER_CITY';
//        $batchRecords[$i]['EMPLOYER_STATE'] = 'EMPLOYER_STATE';
//        $batchRecords[$i]['EMPLOYER_COUNTRY'] = 'EMPLOYER_COUNTRY';
//        $batchRecords[$i]['EMPLOYER_PIN'] = 'EMPLOYER_PIN';
//        $batchRecords[$i]['EMPLOYER_CONTACT_NUM'] = 'EMPLOYER_CONTACT_NUM';
//        $batchRecords[$i]['MINOR_FLG'] = 'MINOR_FLG';
//        $batchRecords[$i]['MINOR_GUARDIAN_CODE'] = 'MINOR_GUARDIAN_CODE';
//        $batchRecords[$i]['MINOR_GUARDIAN_NAME'] = 'MINOR_GUARDIAN_NAME';
//        $batchRecords[$i]['MINOR_GUARDIAN_ADD1'] = 'MINOR_GUARDIAN_ADD1';
//        $batchRecords[$i]['MINOR_GUARDIAN_ADD2'] = 'MINOR_GUARDIAN_ADD2';
//        $batchRecords[$i]['MINOR_GUARDIAN_CITY'] = 'MINOR_GUARDIAN_CITY';
//        $batchRecords[$i]['MINOR_GUARDIAN_STATE'] = 'MINOR_GUARDIAN_STATE';
//        $batchRecords[$i]['MINOR_GUARDIAN_PIN'] = 'MINOR_GUARDIAN_PIN';
//        $batchRecords[$i]['MINOR_GUARDIAN_COUNTRY'] = 'MINOR_GUARDIAN_COUNTRY';
//        $batchRecords[$i]['MODE_OF_OPERATION'] = 'MODE_OF_OPERATION';
//        $batchRecords[$i]['NOMINATION_FLG'] = 'NOMINATION_FLG';
//        $batchRecords[$i]['NOMINEE_NAME'] = 'NOMINEE_NAME';
//        $batchRecords[$i]['NOMINEE_ADD1'] = 'NOMINEE_ADD1';
//        $batchRecords[$i]['NOMINEE_ADD2'] = 'NOMINEE_ADD2';
//        $batchRecords[$i]['NOMINEE_RELATION_CD'] = 'NOMINEE_RELATION_CD';
//        $batchRecords[$i]['NOMINEE_CITY_CD'] = 'NOMINEE_CITY_CD';
//        $batchRecords[$i]['NOMINEE_MINOR_GUARDIAN_CD'] = 'NOMINEE_MINOR_GUARDIAN_CD';
//        $batchRecords[$i]['NOMINEE_DOB'] = 'NOMINEE_DOB';
//        $batchRecords[$i]['NOMINEE_MINOR_FLAG'] = 'NOMINEE_MINOR_FLAG';
//        $batchRecords[$i]['AMOUNT_OPEN'] = 'AMOUNT_OPEN';
//        $batchRecords[$i]['MODE_OF_PAYMENT_OPEN'] = 'MODE_OF_PAYMENT_OPEN';
//        $batchRecords[$i]['ACCOUNT_NUMBER'] = 'ACCOUNT_NUMBER';
//        $batchRecords[$i]['CUST_ID'] = 'CUST_ID';
//        $batchRecords[$i]['SQLID'] = 'SQLID';
//        $batchRecords[$i]['FINACLE_STATUS'] = 'FINACLE_STATUS';
//        $batchRecords[$i]['UPDATESQL_STATUS'] = 'UPDATESQL_STATUS';
//        $batchRecords[$i]['STAFF_FLG'] = 'STAFF_FLG';
//        $batchRecords[$i]['STAFF_NO'] = 'STAFF_NO';
//        $batchRecords[$i]['MINOR_GUARDIAN_TITLE_CODE'] = 'MINOR_GUARDIAN_TITLE_CODE';
//        $batchRecords[$i]['PASSPORT_DETAILS'] = 'PASSPORT_DETAILS';
//        $batchRecords[$i]['INTRODUCER_TITLE_CODE'] = 'INTRODUCER_TITLE_CODE';
//        $batchRecords[$i]['INTRODUCER_NAME'] = 'INTRODUCER_NAME';
//        $batchRecords[$i]['EXISTING_CUST_FLG'] = 'EXISTING_CUST_FLG';
//        $batchRecords[$i]['ACCT_CURRENCY_CODE'] = 'ACCT_CURRENCY_CODE';
//        $batchRecords[$i]['CUST_ID_VER_FLG'] = 'CUST_ID_VER_FLG';
//        $batchRecords[$i]['ACCT_ID_VER_FLG'] = 'ACCT_ID_VER_FLG';
//        $batchRecords[$i]['SCHM_CODE'] = 'SCHM_CODE';
//        $batchRecords[$i]['ORGANISATION_TYPE'] = 'ORGANISATION_TYPE';
//        $batchRecords[$i]['INTRODUCER_FLG'] = 'INTRODUCER_FLG';
//        $batchRecords[$i]['INTRODUCER_CUST_ID'] = 'INTRODUCER_CUST_ID';
//        $batchRecords[$i]['CUST_CURRENCY_CODE'] = 'CUST_CURRENCY_CODE';
//        $batchRecords[$i]['CREATED_DATE'] = 'CREATED_DATE';
//        $batchRecords[$i]['ACCOUNT_TYPE_ID'] = 'ACCOUNT_TYPE_ID';
//        $batchRecords[$i]['REF_NUM'] = 'REF_NUM';
//        $batchRecords[$i]['DEBIT_MANDATE'] = 'DEBIT_MANDATE';
        $i++;
        foreach($batchArr as $arr)
        {
            $batchRecords[$i]['SYS_ID'] = '';
            $batchRecords[$i]['SOL_ID'] = $arr['sol_id'];
            $batchRecords[$i]['FIRST_HOLDER_SALUTATION'] = strtoupper($arr['title']);
            $batchRecords[$i]['FIRST_HOLDER_NAME'] = $arr['name'];
            $batchRecords[$i]['FIRST_HOLDER_OCCUPATION'] = $arr['occupation'];
            $batchRecords[$i]['FIRST_HOLDER_GENDER'] = $arr['gender'];
            if($arr['date_of_birth'] != '') {
                $batchRecords[$i]['FIRST_HOLDER_DOB'] = $arr['date_of_birth'];
            } else {
                $batchRecords[$i]['FIRST_HOLDER_DOB'] = Util::returnDateFormatted($arr['dob_orig'], "Y-m-d", "d-m-Y");
            }
            $batchRecords[$i]['ADDRESS_TYPE'] = $arr['address_type'];
            $batchRecords[$i]['FIRST_HOLDER_PERM_ADD_1'] = (strlen($arr['address_line1']) > $addLen) ? substr($arr['address_line1'],0,$addLen):$arr['address_line1'];
            $batchRecords[$i]['FIRST_HOLDER_PERM_ADD_2'] = (strlen($arr['address_line2']) > $addLen) ? substr($arr['address_line2'],0,$addLen):$arr['address_line2'];
            $batchRecords[$i]['FIRST_HOLDER_PERM_CITY'] = $arr['city'];
            $batchRecords[$i]['FIRST_HOLDER_PERM_STATE'] = $arr['state'];
            $batchRecords[$i]['FIRST_HOLDER_PERM_COUNTRY'] = 'IN';
            $batchRecords[$i]['FIRST_HOLDER_PERM_PIN'] = $arr['pincode'];
            $batchRecords[$i]['FIRST_HOLDER_COMM_ADD_1'] = (strlen($arr['comm_address_line1']) > $addLen) ? substr($arr['comm_address_line1'],0,$addLen):$arr['comm_address_line1'];
            $batchRecords[$i]['FIRST_HOLDER_COMM_ADD_2'] = (strlen($arr['comm_address_line2']) > $addLen) ? substr($arr['comm_address_line2'],0,$addLen):$arr['comm_address_line2'];
            $batchRecords[$i]['FIRST_HOLDER_COMM_CITY'] = (strlen($arr['comm_city']) > 5) ? substr($arr['comm_city'],0,5):$arr['comm_city'];
            $batchRecords[$i]['FIRST_HOLDER_COMM_STATE'] = $arr['comm_state'];
            $batchRecords[$i]['FIRST_HOLDER_COMM_COUNTRY'] = 'IN';
            $batchRecords[$i]['FIRST_HOLDER_COMM_PIN'] = (strlen($arr['comm_pin']) > $pinLen) ? substr($arr['comm_pin'],0,$pinLen):$arr['comm_pin'];
            $batchRecords[$i]['PHONE_NUM'] = (strlen($arr['landline']) > 10) ? substr($arr['landline'],0,10) : $arr['landline'];
            $batchRecords[$i]['MOBILE_NUM'] = (strlen($arr['mobile']) > 10) ? substr($arr['mobile'],0,10) : $arr['mobile'];
            $batchRecords[$i]['EMAIL_ADD'] = $arr['email'];
            $batchRecords[$i]['PAN'] = $arr['pan'];
            $batchRecords[$i]['UID_NUM'] = $arr['aadhaar_no'];
            $batchRecords[$i]['NRE_FLG'] = $arr['nre_flag'];
            $batchRecords[$i]['NRE_NATIONALITY'] = $arr['nre_nationality'];
            $batchRecords[$i]['PASSPORT_NUM'] = $arr['passport'];
            $batchRecords[$i]['PASSPORT_ISSUE_DT'] = '';
            $batchRecords[$i]['PASSPORT_EXPIRY_DT'] = '';
            $batchRecords[$i]['MARITAL_STATUS'] = $arr['marital_status'];
            $batchRecords[$i]['CUST_COMM_CODE'] = $arr['cust_comm_code'];
            $batchRecords[$i]['OTHER_BANK_AC_NUM'] = $arr['other_bank_account_no'];
            $batchRecords[$i]['OTHER_BANK_AC_TYPE'] = $arr['other_bank_account_type'];
            $batchRecords[$i]['OTHER_BANK_NAME'] = $arr['other_bank_name'];
            $batchRecords[$i]['OTHER_BANK_BRANCH'] = $arr['other_bank_branch'];
            $batchRecords[$i]['EMPLOYER_NAME'] = $arr['employer_name'];
            $batchRecords[$i]['EMPLOYER_ADD1'] = $arr['employer_address_line1'];
            $batchRecords[$i]['EMPLOYER_ADD2'] = $arr['employer_address_line2'];
            $batchRecords[$i]['EMPLOYER_CITY'] = $arr['employer_address_city'];
            $batchRecords[$i]['EMPLOYER_STATE'] = $arr['employer_address_state'];
            $batchRecords[$i]['EMPLOYER_COUNTRY'] = '';
            $batchRecords[$i]['EMPLOYER_PIN'] = $arr['employer_address_pincode'];
            $batchRecords[$i]['EMPLOYER_CONTACT_NUM'] = $arr['employer_contact_no'];
            $batchRecords[$i]['MINOR_FLG'] = $arr['minor_flg'];
            if(strtolower($arr['minor_flg']) == 'y'){
               $minorGuardianCode = 'F';  
            } else if(strtolower($arr['minor_flg']) == 'n'){
               $minorGuardianCode = '';  
            }
            else{
               $minorGuardianCode = $arr['minor_guardian_code'];
            }
            $batchRecords[$i]['MINOR_GUARDIAN_CODE'] = $minorGuardianCode;
//            $batchRecords[$i]['MINOR_GUARDIAN_NAME'] = (strtolower($arr['minor_flg']) == 'y')? '' :$arr['minor_guardian_name'];
            $batchRecords[$i]['MINOR_GUARDIAN_NAME'] = '';
            $batchRecords[$i]['MINOR_GUARDIAN_ADD1'] = $arr['minor_guardian_address_line1'];
            $batchRecords[$i]['MINOR_GUARDIAN_ADD2'] = $arr['minor_guardian_address_line2'];
            $batchRecords[$i]['MINOR_GUARDIAN_CITY'] = $arr['minor_guardian_city'];
            $batchRecords[$i]['MINOR_GUARDIAN_STATE'] = $arr['minor_guardian_state'];
            $batchRecords[$i]['MINOR_GUARDIAN_PIN'] = $arr['minor_guardian_pincode'];
            $batchRecords[$i]['MINOR_GUARDIAN_COUNTRY'] = '';
            $batchRecords[$i]['MODE_OF_OPERATION'] = $arr['mode_of_operation'];
            $batchRecords[$i]['NOMINATION_FLG'] = $arr['nomination_flg'];
            $batchRecords[$i]['NOMINEE_NAME'] = $arr['nominee_name'];
            $batchRecords[$i]['NOMINEE_ADD1'] = $arr['nominee_add_line1'];
            $batchRecords[$i]['NOMINEE_ADD2'] = $arr['nominee_add_line2'];
            $batchRecords[$i]['NOMINEE_RELATION_CD'] = $arr['nominee_relationship'];
            $batchRecords[$i]['NOMINEE_CITY_CD'] = (strlen($arr['nominee_city_cd']) > 5) ? substr($arr['nominee_city_cd'],0,5):$arr['nominee_city_cd'];
            if(!empty($arr['nominee_minor_guradian_cd'])){
                if(intval($arr['nominee_minor_guradian_cd']) > 0)
                {
                   $nomineeMinorGuardianCD = intval($arr['nominee_minor_guradian_cd']);
                }
                else{
                    $nomineeMinorGuardianCD = '';
                }
            }else{
                    $nomineeMinorGuardianCD = '';
                }
            
            $batchRecords[$i]['NOMINEE_MINOR_GUARDIAN_CD'] = $nomineeMinorGuardianCD;
            if($arr['nominee_dob'] != '') {
                $batchRecords[$i]['NOMINEE_DOB'] = ($arr['nominee_dob'] == '00-00-0000' ? '': $arr['nominee_dob']);
            } else {
                $batchRecords[$i]['NOMINEE_DOB'] = Util::returnDateFormatted($arr['nominee_dob_orig'], "Y-m-d", "d-m-Y");
            }
            $batchRecords[$i]['NOMINEE_MINOR_FLAG'] = $arr['nominee_minor_flag'];
            $batchRecords[$i]['AMOUNT_OPEN'] = $arr['amount_open'];
            $batchRecords[$i]['MODE_OF_PAYMENT_OPEN'] = $arr['mode_of_payment_open'];
            $batchRecords[$i]['ACCOUNT_NUMBER'] = $arr['account_no'];
            $batchRecords[$i]['CUST_ID'] = $arr['cust_id'];
            $batchRecords[$i]['SQLID'] = $arr['sqlid'];
            $batchRecords[$i]['FINACLE_STATUS'] = $arr['finacle_status'];
            $batchRecords[$i]['UPDATESQL_STATUS'] = $arr['update_sql_status'];
            $batchRecords[$i]['STAFF_FLG'] = $arr['staff_flg'];
            $batchRecords[$i]['STAFF_NO'] = $arr['staff_no'];
            $batchRecords[$i]['MINOR_GUARDIAN_TITLE_CODE'] = $arr['minor_title_guradian_code'];
            $batchRecords[$i]['PASSPORT_DETAILS'] = $arr['passport_details'];
            $batchRecords[$i]['INTRODUCER_TITLE_CODE'] = $arr['introducer_title_code'];
            $batchRecords[$i]['INTRODUCER_NAME'] = $arr['introducer_name'];
            $batchRecords[$i]['EXISTING_CUST_FLG'] = $arr['existing_cust_flg'];
            $batchRecords[$i]['ACCT_CURRENCY_CODE'] = $arr['account_currency_code'];
            $batchRecords[$i]['CUST_ID_VER_FLG'] = $arr['cust_id_ver_flg'];
            $batchRecords[$i]['ACCT_ID_VER_FLG'] = $arr['account_id_ver_flg'];
            $batchRecords[$i]['SCHM_CODE'] = $arr['schm_code'];
            $batchRecords[$i]['ORGANISATION_TYPE'] = $arr['orgaization_type'];
            $batchRecords[$i]['INTRODUCER_FLG'] = $arr['introducer_flg'];
            $batchRecords[$i]['INTRODUCER_CUST_ID'] = $arr['introducer_cust_id'];
            $batchRecords[$i]['CUST_CURRENCY_CODE'] = $arr['cust_currency_code'];
            $batchRecords[$i]['CREATED_DATE'] = $arr['date_created'];
            $batchRecords[$i]['ACCOUNT_TYPE_ID'] = "SINGLE";
            $batchRecords[$i]['REF_NUM'] = $arr['ref_num'];
            $batchRecords[$i]['DEBIT_MANDATE'] = $arr['debit_mandate_amount'];
            
            $i++;
        }
        return $batchRecords;
        
    }
    
    /*
     * showOutfileDetails , show output file details
     */
    public function showOutputfileDetails($page = 1,$data = array(), $paginate = NULL, $force = FALSE) {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_BOI_OUTPUT_FILE ." as p",array('*'));
        $select->joinleft(DbTable::TABLE_BOI_CORP_CARDHOLDER." as cust","cust.output_file_id = p.id",array('count(`cust`.`output_file_id`) as count'));
        $select->group('cust.output_file_id');
        $select->order('p.id DESC');
        return $this->_paginate($select, $page, $paginate);
    }
    
     public function saveOutputFile($batchName = '') {
        $outData = array(
                    'batch_name' => $batchName,
                    'date_created' =>  new Zend_Db_Expr('NOW()')
                   
                );
       $id = $this->insert($outData);
       return $id;
    }
}
