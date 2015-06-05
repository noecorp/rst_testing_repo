<?php

// Remittance reports
class Remit_Reports extends Remit {

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
    protected $_name = DbTable::TABLE_REMITTERS;

    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Remitters';



    /* public function getList(){
      $select = $this->select()
      ->setIntegrityCheck(false)
      ->from("t_agents as a",array('id'))
      ->joinLeft("t_opertaion as o", "o.id = a.operation_id",array('id','name'))
      ->where('email =?','vikram@transerv.co.in');
      //echo $select->__toString();exit;
      return  $this->fetchAll($select);

      } */




    /*
     *  getRemitterRegistrations function will fetch remitters details registred during a time span
     */
    public function getRemitterRegistrations($param) { 
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['2'];
        } 
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $remitModel = new Remit_Kotak_Remitter();
                $detailsArr = $remitModel->getRemitterRegistrations($param);
                break;
            case $bankUnicodeArr['2']:
                $remitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $remitModel->getRemitterRegistrations($param);
                break;
//            case $bankUnicodeArr['1']:
//                $remitModel = new Remit_Boi_Remitter();
//                $detailsArr = $remitModel->getRemitterRegistrations($param);
//                break;
        } 
        return $detailsArr;
    }

    /* getRemitterTransactions() will return the remitter's remit transaction with date duration wise.
     * Params/Fileters :- remitter id and duration
     */

    public function getRemitterTransactions($param) {
        $remitDuration = $param['duration'];
        $dates = Util::getDurationDates($param['duration']);
        $detailsArr = array();
        
       
        /*         * ** getting remitters remit transactions for specified duration *** */
        //if($remitDuration!='' && $remitterId>=1){
        if ($remitDuration != '') {
            $fromDate = $dates['from'];
            $toDate = $dates['to'];
            $param['from_date'] = $fromDate;
            $param['to_date'] = $toDate;
            $bankUnicodeArr = Util::bankUnicodesArray();
        
            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $objRemitRequest = new Remit_Kotak_Remittancerequest();
                    $detailsArr = $objRemitRequest->getRemitterRemittances($param);
                    break;
                case $bankUnicodeArr['2']:
                    $objRemitRequest = new Remit_Ratnakar_Remittancerequest();
                    $detailsArr = $objRemitRequest->getRemitterRemittances($param);
                    break;
                case $bankUnicodeArr['1']:
                    $objRemitRequest = new Remit_Remittancerequest();
                    $detailsArr = $objRemitRequest->getRemitterRemittances($param);
                    break;
            }
        }
        
        return $detailsArr;
    }

    /* exportRemitterTransactions() will return remitter remit transactions, 
     * As params :- remitter id , date duration will be expected.
     */

    public function exportRemitterTransactions($param) {
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
        $summaryData = $this->getRemitterTransactions($param);
        $retData = array();

        if (!empty($summaryData)) {

            foreach ($summaryData as $key => $data) {

                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['remitter_reg_date'] = Util::returnDateFormatted($data['remitter_reg_date'], "Y-m-d", "d-m-Y", "-");
                $retData[$key]['remitter_name'] = $data['remitter_name'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['bene_name'] = $data['bene_name'];
                $retData[$key]['bene_bank_name'] = $data['bene_bank_name'];
                $retData[$key]['bene_ifsc_code'] = $data['bene_ifsc_code'];
                $retData[$key]['batch_name'] = ( ($param['bank_unicode'] == $bankBoiUnicode) ||  ($param['bank_unicode'] == $bankRatnakarUnicode) ) ? $data['batch_name'] : '';
                if(( $param['bank_unicode'] == $bankRatnakarUnicode) ){
                     $retData[$key]['utr'] = $data['utr'];
                     $retData[$key]['txn_code'] = $data['txn_code'];
                     $retData[$key]['status'] = $data['status'];
                 }   
            }
        }

        return $retData;
    }

    /* getRemittanceRefunds() will return the remittance refunds.
     * Params/Fileters :- to date and from date 
     */

    public function getRemittanceRefunds($param) {
        $toDate = $param['to_date'];
        $fromDate = $param['from_date'];
        $detailsArr = array();

        if ($toDate != '' && $fromDate != '') {

            /*             * ** getting remittance refunds *** */
            $param['to_date'] = $toDate;
            $param['from_date'] = $fromDate;
            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $remitRequest = new Remit_Kotak_Remittancerequest();
                    $detailsArr = $remitRequest->getRemittanceRefunds($param);
                    break;
                case $bankUnicodeArr['2']:
                    $remitRequest = new Remit_Ratnakar_Remittancerequest();
                    $detailsArr = $remitRequest->getRemittanceRefunds($param);
                    break;
//                case $bankUnicodeArr['1']:
//                    $remitRequest = new Remit_Remittancerequest();
//                    $detailsArr = $remitRequest->getRemittanceRefunds($param);
//                    break;
            }
        } 

        return $detailsArr;
    }

    /* exportRemittanceRefunds() will return remitter remit transactions, 
     * As params :- remitter id , date duration will be expected.
     */

    public function exportRemittanceRefunds($param) {

        $summaryData = $this->getRemittanceRefunds($param);
        $retData = array();

        if (!empty($summaryData)) { 
            foreach ($summaryData as $key => $data) { 
                $retData[$key]['refund_date'] = Util::returnDateFormatted($data['refund_date'], "Y-m-d", "d-m-Y", "-");
                $retData[$key]['sup_dist_code'] = $data['sup_dist_code'];
                $retData[$key]['sup_dist_name'] = $data['sup_dist_name'];
                $retData[$key]['dist_code'] = $data['dist_code'];
                $retData[$key]['dist_name'] = $data['dist_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['remitter_name'] = $data['remitter_name'];
                $retData[$key]['remitter_mobile_number'] = $data['remitter_mobile_number'];
                $retData[$key]['remitter_email'] = $data['remitter_email'];
                $retData[$key]['beneficiary_name'] = $data['beneficiary_name'];
                $retData[$key]['card_number'] = Util::maskCard($data['card_number']);
                $retData[$key]['crn'] = Util::maskCard($data['crn']);
                $retData[$key]['beneficiary_bank_account_number'] = $data['beneficiary_bank_account_number'];
                $retData[$key]['request_txn_code'] = $data['request_txn_code'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                $retData[$key]['refund_amount'] = Util::numberFormat($data['refund_amount']);
                $retData[$key]['remarks'] = $data['remarks'];
                $retData[$key]['reversal_fee'] = Util::numberFormat($data['reversal_fee']);
                $retData[$key]['reversal_service_tax'] = Util::numberFormat($data['reversal_service_tax']);
                $retData[$key]['utr'] = $data['utr'];
                $retData[$key]['status'] = ucfirst($data['status']);              
            }
        }

        return $retData;
    }

    /* getRemittanceRefundYetToClaim() will return remittance refund yet to claim, 
     * As params :- date duration will be expected.
     */

    public function getRemittanceRefundYetToClaim($param) {

        $duration = isset($param['dur']) ? $param['dur'] : '';
        $dates = Util::getDurationDates($duration);
        $detailsArr = array();
        if (!empty($dates) || (!empty($param['to']) && !empty($param['from']))) {
            if (!empty($dates)) {
                $to = isset($dates['to']) ? $dates['to'] : '';
                $toArr = explode(" ", $to);
                $param['to'] = $toArr[0];
                $from = isset($dates['from']) ? $dates['from'] : '';
                $fromArr = explode(" ", $from);
                $param['from'] = $fromArr[0];
            } else {
                $param['to'] = $param['to'];
                $param['from'] = $param['from'];
            }
            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $remittanceStatusLog = new Remit_Kotak_Remittancestatuslog();
                    $detailsArr = $remittanceStatusLog->getRemittanceRefundYetToClaim($param);
                    break;
                case $bankUnicodeArr['2']:
                    $remittanceStatusLog = new Remit_Ratnakar_Remittancestatuslog();
                    $detailsArr = $remittanceStatusLog->getRemittanceRefundYetToClaim($param);
                    break;
                case $bankUnicodeArr['1']:
                    $remittanceStatusLog = new Remit_Remittancestatuslog();
                    $detailsArr = $remittanceStatusLog->getRemittanceRefundYetToClaim($param);
                    break;
            }
        } // date check if

        return $detailsArr;
    }

    public function exportRemittanceRefundYetToClaim($param) {
        $refundData = $this->getRemittanceRefundYetToClaim($param);
        $retData = array();
        $totalRefundData = count($refundData);

        for ($i = 0; $i < $totalRefundData; $i++) {
            $dtCreated = explode(" ", $refundData[$i]['date_created']);
            $retData[$i]['date_created'] = Util::returnDateFormatted($dtCreated[0], "Y-m-d", "d-m-Y", "-");
            $retData[$i]['remitter_name'] = $refundData[$i]['remitter_name'];
            $retData[$i]['remitter_mobile'] = $refundData[$i]['remitter_mobile'];
            $retData[$i]['remitter_email'] = $refundData[$i]['remitter_email'];
            $retData[$i]['beneficiary_name'] = $refundData[$i]['beneficiary_name'];
            $retData[$i]['bank_account_number'] = $refundData[$i]['bank_account_number'];
            $retData[$i]['txn_code'] = $refundData[$i]['txn_code'];
            $retData[$i]['amount'] = $refundData[$i]['amount'];
            $retData[$i]['remarks'] = $refundData[$i]['remarks'];
            $retData[$i]['fee'] = Util::numberFormat($refundData[$i]['fee']);
            $retData[$i]['service_tax'] = Util::numberFormat($refundData[$i]['service_tax']);
            $retData[$i]['sup_dist_code'] = $refundData[$i]['sup_dist_code'];
            $retData[$i]['sup_dist_name'] = $refundData[$i]['sup_dist_name'];
            $retData[$i]['dist_code'] = $refundData[$i]['dist_code'];
            $retData[$i]['dist_name'] = $refundData[$i]['dist_name'];
            $retData[$i]['agent_code'] = $refundData[$i]['agent_code'];
            $retData[$i]['agent_name'] = $refundData[$i]['agent_name'];
            $retData[$i]['agent_bank'] = $refundData[$i]['bank_name'];
            $retData[$i]['mobile'] = $refundData[$i]['mobile'];
            $retData[$i]['utr'] = $refundData[$i]['utr'];
            $retData[$i]['trxn_code'] = $refundData[$i]['txn_code'];            
            $retData[$i]['date_utr'] = $refundData[$i]['date_utr'];
            $retData[$i]['status_utr'] = $refundData[$i]['status_utr'];
            $retData[$i]['date_status_response'] = $refundData[$i]['date_status_response'];
            $retData[$i]['status_response'] = $refundData[$i]['status_response'];
            $retData[$i]['status'] = $refundData[$i]['status'];
            $retData[$i]['batch_name'] = $refundData[$i]['batch_name'];
            $retData[$i]['batch_date'] = $refundData[$i]['batch_date'];
            $retData[$i]['neft_processed'] = $refundData[$i]['neft_processed'];
            $retData[$i]['neft_processed_date'] = $refundData[$i]['neft_processed_date'];
            $retData[$i]['status_sms'] = $refundData[$i]['status_sms'];
            $retData[$i]['date_updated'] = $refundData[$i]['date_updated'];
        }

        return $retData;
    }

    /* getRemittanceException() will return the remittance exception.
     * Params/Fileters :- to date and from date 
     */

    public function getRemittanceException($param) {

        $toDate = $param['to_date'];
        $fromDate = $param['from_date'];
        $detailsArr = array();
        if ($toDate != '' && $fromDate != '') {


            $params = array('to' => $toDate, 'from' => $fromDate, 'noofrecords' => $param['noofrecords']);


            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $remitRequest = new Remit_Kotak_Remittancerequest();
                    $detailsArr = $remitRequest->getRemittanceException($params);
                    break;
                case $bankUnicodeArr['2']:
                    $remitRequest = new Remit_Ratnakar_Remittancerequest();
                    $detailsArr = $remitRequest->getRemittanceException($params);
                    break;
                case $bankUnicodeArr['1']:
                    $remitRequest = new Remit_Remittancerequest();
                    $detailsArr = $remitRequest->getRemittanceException($params);
                    break;
            }
        } 

        return $detailsArr;
    }

    /* exportRemittanceException() will return remittance exception
     * As params :- date will be expected.
     */

    public function exportRemittanceException($param) {

        $remittanceData = $this->getRemittanceException($param);
        $retData = array();

        if (!empty($remittanceData)) {

            foreach ($remittanceData as $key => $data) {
                $dateArr = explode(" ", $data['date_created']);
                $retData[$key]['created_date'] = Util::returnDateFormatted($dateArr[0], "Y-m-d", "d-m-Y", "-");
                $retData[$key]['remitter_name'] = $data['remitter_name'];
                $retData[$key]['remitter_mobile_number'] = $data['remitter_mobile_number'];
                $retData[$key]['remitter_email'] = $data['remitter_email'];
                $retData[$key]['beneficiary_name'] = $data['beneficiary_name'];
                $retData[$key]['total_amount'] = $data['total_amount'];
                $retData[$key]['total_count'] = $data['total_count'];
                $retData[$key]['bank_account_number'] = $data['bank_account_number'];
                $retData[$key]['sup_dist_code'] = $data['sup_dist_code'];
                $retData[$key]['sup_dist_name'] = $data['sup_dist_name'];
                $retData[$key]['dist_code'] = $data['dist_code'];
                $retData[$key]['dist_name'] = $data['dist_name'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['ifsc_code'] = $data['ifsc_code'];
            }
        }

        return $retData;
    }

    /* getRemittance() will return the data array of agents transactions like remittance, 
     * remittance refund, remittance fee and service tax date wise
     * As params , date duration will be expected.
     */

    public function getRemittance($param) {

        $params = explode('&', $param['params']);
        $bankUnicode = explode('=', $params[0]);
        $dateFrom = explode('=', $params[1]);
        $dateTo = explode('=', $params[2]);
        $mobile = explode('=', $params[3]);
        $txnno = explode('=', $params[4]);
	$product_id = explode('=', $params[5]);
        
        $param = array(
                    'from' => $dateFrom[1].' 00:00:00',
                    'to' => $dateTo[1].' 23:59:59',
                    'mobile_no' => $mobile[1],
                    'txn_no' => $txnno[1],
                    'bank_unicode' => $bankUnicode[1],
		    'product_id' => $product_id[1]
                );
        
        $detailsArr = array();
        if (!empty($param)) {

            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }
		
            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $objRemitter = new Remit_Kotak_Remittancerequest();
                    $detailsArr = $objRemitter->getKotakRemittance($param);
                    break;
                case $bankUnicodeArr['2']:
                    $objRemitter = new Remit_Ratnakar_Remittancerequest();
                    $detailsArr = $objRemitter->getRatnakarRemittance($param);
                    break;
                case $bankUnicodeArr['1']:
                    $objRemitter = new Remit_Boi_Remitter();
                    $detailsArr = $objRemitter->getBoiRemittance($param);
                    break;
            }
        } // date check if
        return $detailsArr;
    }

    /* exportRemittance() will find data for agents transactions like remittance, 
     * remittance refund, remittance fee and service tax
     * it will accept param array with query filters e.g.. duration of report
     */

    public function exportRemittance($param) {
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
        $bankunicode = '';
        if(isset($param['bank_unicode'])){
        $bankunicode = $param['bank_unicode'];
        }
        $data = $this->getRemittance($param);
        
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $agentUser = new AgentUser();
        
        if (!empty($data)) {

            foreach ($data as $key => $data) {

                $retData[$key]['txn_date'] = $data['txn_date'];
                
                $agentUser = new AgentUser();
                $agentType = $agentUser->getAgentCodeName($data['agent_user_type'], $data['agent_id']);

                if(!empty($agentType))
                {
                    $retData[$key] = array_merge($retData[$key], $agentType);
                }

                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_mobile'] = $data['agent_mobile'];
                $retData[$key]['agent_email'] = $data['agent_email'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['estab_city'] = $data['estab_city'];
                $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['txn_type']];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['mobile_number'] = $data['mobile_number'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                //$retData[$key]['batch_name'] = ($param['bank_unicode'] == $bankBoiUnicode) ? $data['batch_name'] : '';
                
                if(isset($data['rmid']))
                {
                    $refund_txn = $this->getRefundTxnRefNo($data['rmid'],$bankunicode);

                    if(!empty($refund_txn))
                    {
                        $retData[$key]['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    }
                    else
                    {
                        $retData[$key]['refund_txn_code'] = '';
                    }
                }
                elseif(isset($data['refund_txn_code']))
                {
                    $retData[$key]['refund_txn_code'] = $data['refund_txn_code'];
                }
                else
                {
                    $retData[$key]['refund_txn_code'] = '';
                }
                
                if($data['txn_status'] == STATUS_IN_PROCESS) {
                                $txn_status  = 'In Process';
                            } else {
                                $txn_status  = ucwords($data['txn_status']);                                
                    }
               // $retData[$key]['utr_no'] = '';
                $retData[$key]['remit_name'] = $data['remit_name'];
                $retData[$key]['remit_mobile_number'] = $data['mobile_number'];
                $retData[$key]['remitter_email'] = $data['remitter_email'];
                $retData[$key]['remit_regn_date'] = $data['remit_regn_date'];
                $retData[$key]['bene_name'] = $data['bene_name'];
                $retData[$key]['bene_bankname'] = $data['bene_bankname'];
                $retData[$key]['bene_ifsccode'] = $data['bene_ifsccode'];
                $retData[$key]['txn_status'] = $txn_status;
                $reason = explode(')', $data['final_response']);
                $retData[$key]['reason'] = ltrim($reason[1]);
                $retData[$key]['reason_code'] = str_replace('(', '', $reason[0]);
                $retData[$key]['utr_no'] = ( ($param['bank_unicode'] == $bankRatnakarUnicode) ) ? $data['utr'] : '';
                $retData[$key]['batch_name'] = $data['batch_name'];
	
	
            }
           
        }
        return $retData;
    }

    /* getAgentWiseRemittance() will get agent transactions including remittance,refund, all remit txn fees, service tax
     * As params , agent id , date duration will be expected.
     */

    public function getAgentWiseRemittance($param) {

        $detailsArr = array();
        
        if (!empty($param)) {

            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $objRemitter = new Remit_Kotak_Remitter();
                    $detailsArr = $objRemitter->getKotakRemittance($param);
                    break;
                case $bankUnicodeArr['2']:
                    $objRemitter = new Remit_Ratnakar_Remitter();
                    $detailsArr = $objRemitter->getRatnakarRemittance($param);
                    break;
                case $bankUnicodeArr['1']:
                    $objRemitter = new Remit_Boi_Remitter();
                    $detailsArr = $objRemitter->getBoiRemittance($param);
                    break;
            }
        } // date check if
        return $detailsArr;
    }

    /* exportAgentWiseTransactions function will find data for agent wise transaction report. 
     * it will accept param array with query filters e.g.. duration and agent id
     */

    public function exportAgentWiseRemittance($param) {
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
        $data = $this->getAgentWiseRemittance($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
         $status = Zend_Registry::get("REMIT_STATUS");

        if (!empty($data)) {

            foreach ($data as $key => $data) {
                $retData[$key]['txn_date'] = $data['txn_date'];
                $retData[$key]['name'] = $agentInfo['name'];
                $retData[$key]['agent_code'] = $agentInfo['agent_code'];
                $retData[$key]['estab_city'] = $agentInfo['estab_city'];
                $retData[$key]['estab_pincode'] = $agentInfo['estab_pincode'];
                $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['txn_type']];
                $retData[$key]['amount'] = $data['amount'];
                //$retData[$key]['crn']               = $data['crn'];
                $retData[$key]['mobile_number'] = $data['mobile_number'];
                $retData[$key]['txn_code'] = $data['txn_code'];
                //$retData[$key]['batch_name'] = ( ($param['bank_unicode'] == $bankBoiUnicode) ||  ($param['bank_unicode'] == $bankRatnakarUnicode) ) ? $data['batch_name'] : '';
                
                $retData[$key]['sup_dist_code'] = $data['sup_dist_code'];
                $retData[$key]['sup_dist_name'] = $data['sup_dist_name'];
                $retData[$key]['dist_code'] = $data['dist_code'];
                $retData[$key]['dist_name'] = $data['dist_name'];
                $retData[$key]['utr'] = $data['utr'];
                $retData[$key]['remit_name'] = $data['remit_name'];
                $retData[$key]['remit_mobile_number1'] = $data['mobile_number'];
                $retData[$key]['remitter_email'] = $data['remitter_email'];
                $retData[$key]['remit_regn_date'] = $data['remit_regn_date'];
                $retData[$key]['bene_name'] = $data['bene_name'];
                $retData[$key]['bene_bankname'] = $data['bene_bankname'];
                $retData[$key]['bene_ifsccode'] = $data['bene_ifsccode'];
                
                $retData[$key]['txn_status'] = $status[$data['txn_status']];
                $retData[$key]['returned_date'] = $data['returned_date'];
                $retData[$key]['rejection_code'] = $data['rejection_code'];
                $retData[$key]['rejection_remark'] = $data['rejection_remark'];
            
                $retData[$key]['plan_commission_name'] = $data['plan_commission_name'];
                $retData[$key]['transaction_fee'] = $data['transaction_fee'];
                $retData[$key]['transaction_service_tax'] = $data['transaction_service_tax'];
                $retData[$key]['commission_amount'] = $data['commission_amount'];
                
                if( ($param['bank_unicode'] == $bankBoiUnicode) || ($param['bank_unicode'] == $bankRatnakarUnicode) ){
                    
                    //explode batch date and time
                    $batch_datetime =  $data['batch_date'];
                    $batch_datetime_arr = explode(' ', $batch_datetime);
                    $batch_date = $batch_datetime_arr[0];
                    $batch_time = $batch_datetime_arr[1];
                    
                    $retData[$key]['batch_name'] = $data['batch_name'];
                    $retData[$key]['batch_date'] = $batch_date;
                    $retData[$key]['batch_time'] = $batch_time;
                }
                
            }
            
            
            
            
            
        }

        return $retData;
    }

    public function getRemittanceResponse($params) {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($params['bank_unicode']) || $params['bank_unicode'] == '') {
            $params['bank_unicode'] = $bankUnicodeArr['1'];
        }
        switch ($params['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $remitRequest = new Remit_Kotak_Remittancerequest();
                $detailsArr = $remitRequest->getRemittanceResp($params);
                break;
            case $bankUnicodeArr['2']:
                $remitRequest = new Remit_Ratnakar_Remittancerequest();
                $detailsArr = $remitRequest->getRemittanceResp($params);
                break;
            case $bankUnicodeArr['1']:
                $remitRequest = new Remit_Remittancerequest();
                $detailsArr = $remitRequest->getneftResponse($params);
                break;
        }
        return $detailsArr;
    }

    public function exportneftResponse($param) {
        $bank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bank->bank->unicode;
        
        $RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $bankRatnakarUnicode = $RatnakarBank->bank->unicode;
       
        $queryArray = $this->getRemittanceResponse($param,$page = 1, FALSE);
        $retData = array();
        foreach ($queryArray as $key => $queryArr) {

            
           
            if( ($param['bank_unicode'] == $bankKotakUnicode) ) {
                $retData[$key]['date_created'] = $queryArr['date_created'];
                $retData[$key]['remitter_name'] = $queryArr['remitter_name'];
                $retData[$key]['remitter_mobile_number'] = $queryArr['remitter_mobile_number'];
                $retData[$key]['beneficiary_bank_account_number'] = $queryArr['beneficiary_bank_account_number'];
                $retData[$key]['beneficiary_name'] = $queryArr['beneficiary_name'];
                $retData[$key]['txn_code'] = $queryArr['txn_code'];
                $retData[$key]['amount'] = $queryArr['amount'];
                $retData[$key]['status'] = $queryArr['status'];
                $retData[$key]['remarks'] = $queryArr['remarks'];
            }elseif(($param['bank_unicode'] == $bankRatnakarUnicode) ){
                
                //explode batch date and time
                $batch_datetime =  $queryArr['batch_date'];
                $batch_datetime_arr = explode(' ', $batch_datetime);
                $batch_date = $batch_datetime_arr[0];
                $batch_time = $batch_datetime_arr[1];
                
                $retData[$key]['date_created'] = $queryArr['date_created'];
                $retData[$key]['batch_name'] = $queryArr['batch_name'];
                $retData[$key]['batch_date'] = $batch_date;
                $retData[$key]['batch_time'] = $batch_time;
                $retData[$key]['returned_date'] = $queryArr['returned_date'];
                $retData[$key]['remitter_name'] = $queryArr['remitter_name'];
                $retData[$key]['remitter_mobile_number'] = $queryArr['remitter_mobile_number'];
                $retData[$key]['beneficiary_bank_account_number'] = $queryArr['beneficiary_bank_account_number'];
                $retData[$key]['beneficiary_name'] = $queryArr['beneficiary_name'];
                $retData[$key]['txn_code'] = $queryArr['txn_code'];
                $retData[$key]['utr'] = $queryArr['utr'];
                $retData[$key]['amount'] = $queryArr['amount'];
                $retData[$key]['status'] = Util::getNeftStatus($queryArr['status']);
                $retData[$key]['remarks'] = $queryArr['remarks'];
                $retData[$key]['manual_mapping_remarks'] = $queryArr['manual_mapping_remarks'];
                $retData[$key]['rejection_code'] = $queryArr['rejection_code'];
                $retData[$key]['rejection_remark'] = $queryArr['rejection_remark'];
                
            }else{
                $retData[$key]['date_created'] = $queryArr['date_created'];
                $retData[$key]['batch_name'] = $queryArr['batch_name'];
                $retData[$key]['remitter_name'] = $queryArr['remitter_name'];
                $retData[$key]['remitter_mobile_number'] = $queryArr['remitter_mobile_number'];
                $retData[$key]['beneficiary_bank_account_number'] = $queryArr['beneficiary_bank_account_number'];
                $retData[$key]['beneficiary_name'] = $queryArr['beneficiary_name'];
                $retData[$key]['txn_code'] = $queryArr['txn_code'];
                $retData[$key]['amount'] = $queryArr['amount'];
                $retData[$key]['status'] = Util::getNeftStatus($queryArr['status']);
                $retData[$key]['remarks'] = $queryArr['remarks'];
            }
        }
        return $retData;
    }
             /* exportAgentWiseRemittanceFromAgent function will find data for agent wise transaction report. 
    * it will accept param array with query filters e.g.. duration and agent id
    */
    public function exportAgentWiseRemittanceFromAgent($param){ 
        
        $data = $this->getAgentWiseRemittance($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $status = Zend_Registry::get("REMIT_STATUS");
        
        if(!empty($data))
        {
                                  
            foreach($data as $key=>$data){
//                    $retData[$key]['txn_date']          = $data['txn_date'];
//                    $retData[$key]['name']              = $agentInfo['name'];
//                    $retData[$key]['agent_code']        = $agentInfo['agent_code'];
//                    $retData[$key]['estab_city']        = $agentInfo['estab_city'];
//                    $retData[$key]['estab_pincode']     = $agentInfo['estab_pincode'];
//                    $retData[$key]['txn_type']          = $TXN_TYPE_LABELS[$data['txn_type']]; 
//                    $retData[$key]['amount']            = $data['amount'];
//                    //$retData[$key]['crn']               = $data['crn'];
//                    $retData[$key]['mobile_number']     = $data['mobile_number'];
//                    $retData[$key]['ecs_product_code']  = $data['ecs_product_code'];
//                    $retData[$key]['txn_code']          = $data['txn_code'];
//                    $retData[$key]['txn_status'] = $status[$data['txn_status']];
//                    $retData[$key]['utr'] = $data['utr'];
                   
		    
		$reason = explode(')', $data['final_response']); 
		
		$retData[$key]['txn_date']	=   $data['txn_date'];
		$retData[$key]['sup_dist_code']	=   $data['sup_dist_code'];
		$retData[$key]['sup_dist_name']	=   $data['sup_dist_name'];
		$retData[$key]['dist_code']	=   $data['dist_code'];
		$retData[$key]['dist_name']	=   $data['dist_name'];
		$retData[$key]['agent_code']	=   $data['agent_code'];
		$retData[$key]['agent_mobile']	=   $data['agent_mobile'];
		$retData[$key]['agent_email']	=   $data['agent_email'];
		$retData[$key]['agent_name']	=   $data['agent_name'];
		$retData[$key]['estab_city']	=   $data['estab_city'];
		$retData[$key]['estab_pincode']	=   $data['estab_pincode'];
		$retData[$key]['txn_type']	=   $TXN_TYPE_LABELS[$data['txn_type']];
		$retData[$key]['amount']	=   Util::numberFormat($data['amount']);
		$retData[$key]['mobile_number']	=   $data['mobile_number'];
		$retData[$key]['txn_code']	=   $data['txn_code'];
		$retData[$key]['refund_txn_code']   =   $data['refund_txn_code'];
		$retData[$key]['remit_name']	=   $data['remit_name'];
		$retData[$key]['remit_number']	=   $data['mobile_number'];
		$retData[$key]['remitter_email']    =   $data['remitter_email'];
		$retData[$key]['remit_regn_date']   =   $data['remit_regn_date'];
		$retData[$key]['bene_name']	=   $data['bene_name'];
		$retData[$key]['bene_bankname']	=   $data['bene_bankname'];
		$retData[$key]['bene_ifsccode']	=   $data['bene_ifsccode'];
		$retData[$key]['ben_account_number']=   $data['ben_account_number'];
		$retData[$key]['txn_status']	=   ucfirst($data['txn_status']);  
		$retData[$key]['reason']	=   ltrim($reason[1]);
		$retData[$key]['reason_code']	=   str_replace('(', '', $reason[0]); 
          }
        }
        
        return $retData;
   }
    /* exportRATAgentWiseRemittanceFromAgent function will find data of Ratnakar for agent wise transaction report. 
    * it will accept param array with query filters e.g.. duration and agent id
    */
    public function exportRATAgentWiseRemittanceFromAgent($param){ 
        
        $data = $this->getAgentWiseRemittance($param);
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $status = Zend_Registry::get("REMIT_STATUS");
        
        if(!empty($data))
        {
                                  
            foreach($data as $key=>$data){
//                    $retData[$key]['txn_date']          = $data['txn_date'];
//                    $retData[$key]['name']              = $agentInfo['name'];
//                    $retData[$key]['agent_code']        = $agentInfo['agent_code'];
//                    $retData[$key]['estab_city']        = $agentInfo['estab_city'];
//                    //$retData[$key]['estab_pincode']     = $agentInfo['estab_pincode'];
//                    $retData[$key]['txn_type']          = $TXN_TYPE_LABELS[$data['txn_type']]; 
//                    $retData[$key]['amount']            = $data['amount'];
//                    //$retData[$key]['crn']               = $data['crn'];
//                    $retData[$key]['mobile_number']     = $data['mobile_number'];
//                    $retData[$key]['ben_account_number']  = $data['ben_account_number'];
//                  //  $retData[$key]['ecs_product_code']  = $data['ecs_product_code'];
//                    $retData[$key]['txn_code']          = $data['txn_code'];
//                    $retData[$key]['txn_status'] = $status[$data['txn_status']];
//                    $retData[$key]['utr'] = $data['utr'];
//                    if(isset($data['flag'])){
//                    	if($data['flag']==2){
//                    		$retData[$key]['transfer_mode'] = "IMPS";
//                    	}else{
//                    		$retData[$key]['transfer_mode'] = "NEFT";
//                    	}
//                    	 
//                    }
//		
		$reason = explode(')', $data['final_response']); 
		
		$retData[$key]['txn_date']	=   $data['txn_date'];
		$retData[$key]['sup_dist_code']	=   $data['sup_dist_code'];
		$retData[$key]['sup_dist_name']	=   $data['sup_dist_name'];
		$retData[$key]['dist_code']	=   $data['dist_code'];
		$retData[$key]['dist_name']	=   $data['dist_name'];
		$retData[$key]['agent_code']	=   $data['agent_code'];
		$retData[$key]['agent_mobile']	=   $data['agent_mobile'];
		$retData[$key]['agent_email']	=   $data['agent_email'];
		$retData[$key]['agent_name']	=   $data['agent_name'];
		$retData[$key]['estab_city']	=   $data['estab_city'];
		$retData[$key]['estab_pincode']	=   $data['estab_pincode'];
		$retData[$key]['txn_type']	=   $TXN_TYPE_LABELS[$data['txn_type']];
		$retData[$key]['amount']	=   Util::numberFormat($data['amount']);
		$retData[$key]['mobile_number']	=   $data['mobile_number'];
		$retData[$key]['txn_code']	=   $data['txn_code'];
		$retData[$key]['refund_txn_code']   =   $data['refund_txn_code'];
		$retData[$key]['remit_name']	=   $data['remit_name'];
		$retData[$key]['remit_number']	=   $data['mobile_number'];
		$retData[$key]['remitter_email']    =   $data['remitter_email'];
		$retData[$key]['remit_regn_date']   =   $data['remit_regn_date'];
		$retData[$key]['bene_name']	=   $data['bene_name'];
		$retData[$key]['bene_bankname']	=   $data['bene_bankname'];
		$retData[$key]['bene_ifsccode']	=   $data['bene_ifsccode'];
		$retData[$key]['ben_account_number']=   $data['ben_account_number'];
		$retData[$key]['txn_status']	=   ucfirst($data['txn_status']);  
		$retData[$key]['reason']	=   ltrim($reason[1]);
		$retData[$key]['reason_code']	=   str_replace('(', '', $reason[0]);
		$retData[$key]['utr']		=   $data['utr'];
		$retData[$key]['batch_name']	=   $data['batch_name'];
		$retData[$key]['flag']		=   ($data['flag'] == 2) ? TXN_IMPS : TXN_NEFT ; 
	    }
        }
        return $retData;
   }
   
   /* exportAgentWiseRemittanceFromDistributorAgent function will find data for agent wise transaction report. 
    * it will accept param array with query filters e.g.. duration and agent id
    */
    public function exportAgentWiseRemittanceFromDistributorAgent($param){ 
        
      //  $data = $this->getAgentWiseRemittance($param);
        //$data = $this->getRatnakarMultiRemittance($param);
         $objRemitter = new Remit_Ratnakar_Remitter();
         $data = $objRemitter->getRatnakarMultiRemittance($param);
       
        $objAgent = new Agents();
        $agentInfo = $objAgent->findById($param['agent_id']);
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $status = Zend_Registry::get("REMIT_STATUS");
        
        if(!empty($data))
        {
                                
            foreach($data as $key=>$data){
//                    $retData[$key]['txn_date']          = $data['txn_date'];
//                    $retData[$key]['name']              = $data['agent_name'];
//                    $retData[$key]['agent_code']        = $data['agent_code'];
//                    $retData[$key]['estab_city']        = $data['estab_city'];
//                    $retData[$key]['txn_type']          = $TXN_TYPE_LABELS[$data['txn_type']]; 
//                    $retData[$key]['amount']            = $data['amount'];
//                    //$retData[$key]['crn']               = $data['crn'];
//                    $retData[$key]['mobile_number']     = $data['mobile_number'];
//                  //  $retData[$key]['ecs_product_code']  = $data['ecs_product_code'];
//                    $retData[$key]['txn_code']          = $data['txn_code'];
//                    $retData[$key]['txn_status'] = $status[$data['txn_status']];
//                    $retData[$key]['utr'] = $data['utr'];
	    
		    $reason = explode(')', $data['final_response']); 
		
		$retData[$key]['txn_date']	=   $data['txn_date'];
		$retData[$key]['sup_dist_code']	=   $data['sup_dist_code'];
		$retData[$key]['sup_dist_name']	=   $data['sup_dist_name'];
		$retData[$key]['dist_code']	=   $data['dist_code'];
		$retData[$key]['dist_name']	=   $data['dist_name'];
		$retData[$key]['agent_code']	=   $data['agent_code'];
		$retData[$key]['agent_mobile']	=   $data['agent_mobile'];
		$retData[$key]['agent_email']	=   $data['agent_email'];
		$retData[$key]['agent_name']	=   $data['agent_name'];
		$retData[$key]['estab_city']	=   $data['estab_city'];
		$retData[$key]['estab_pincode']	=   $data['estab_pincode'];
		$retData[$key]['txn_type']	=   $TXN_TYPE_LABELS[$data['txn_type']];
		$retData[$key]['amount']	=   Util::numberFormat($data['amount']);
		$retData[$key]['mobile_number']	=   $data['mobile_number'];
		$retData[$key]['txn_code']	=   $data['txn_code'];
		$retData[$key]['refund_txn_code']   =   $data['refund_txn_code'];
		$retData[$key]['remit_name']	=   $data['remit_name'];
		$retData[$key]['remit_number']	=   $data['mobile_number'];
		$retData[$key]['remitter_email']    =   $data['remitter_email'];
		$retData[$key]['remit_regn_date']   =   $data['remit_regn_date'];
		$retData[$key]['bene_name']	=   $data['bene_name'];
		$retData[$key]['bene_bankname']	=   $data['bene_bankname'];
		$retData[$key]['bene_ifsccode']	=   $data['bene_ifsccode'];
		$retData[$key]['ben_account_number']=   $data['ben_account_number'];
		$retData[$key]['txn_status']	=   ucfirst($data['txn_status']);  
		$retData[$key]['reason']	=   ltrim($reason[1]);
		$retData[$key]['reason_code']	=   str_replace('(', '', $reason[0]);
		$retData[$key]['utr']		=   $data['utr'];
		$retData[$key]['batch_name']	=   $data['batch_name'];
		$retData[$key]['flag']		=   ($data['flag'] == 2) ? TXN_IMPS : TXN_NEFT ; 
                     
          }
        }
       
      
        return $retData;
   }
   
   /* exportAgentWiseRemittanceFromSuperDistributorAgent function will find data for agent wise transaction report. 
    * it will accept param array with query filters e.g.. duration and agent id
    */
    public function exportAgentWiseRemittanceFromSuperDistributorAgent($param){ 
        
        //$data = $this->getAgentWiseRemittance($param);
        $objRemitter = new Remit_Ratnakar_Remitter();
        $data = $objRemitter->getRatnakarMultiRemittance($param);
       
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $status = Zend_Registry::get("REMIT_STATUS");
        
        if(!empty($data))
        {
                                  
            foreach($data as $key=>$data){
                    $retData[$key]['txn_date']          = $data['txn_date'];
                    $retData[$key]['dist_name']         = $data['dist_name'];
                    $retData[$key]['dist_code']         = $data['dist_code'];
                    $retData[$key]['name']              = $data['agent_name'];
                    $retData[$key]['agent_code']        = $data['agent_code'];
                    $retData[$key]['estab_city']        = $data['estab_city'];
                    $retData[$key]['txn_type']          = $TXN_TYPE_LABELS[$data['txn_type']]; 
                    $retData[$key]['amount']            = $data['amount'];
                    //$retData[$key]['crn']               = $data['crn'];
                    $retData[$key]['mobile_number']     = $data['mobile_number'];
                  //  $retData[$key]['ecs_product_code']  = $data['ecs_product_code'];
                    $retData[$key]['txn_code']          = $data['txn_code'];
                    $retData[$key]['txn_status'] = $status[$data['txn_status']];
                    $retData[$key]['utr'] = $data['utr'];
                     
          }
        }
     
        return $retData;
   }
   
    /* get Refund/Reversed Transaction Reference No for a Remittance Request */
    public function getRefundTxnRefNo($rmid,$bankunicode='')
    {
        $bankUnicodeArr = Util::bankUnicodesArray();
        $tableName = '';
            switch ($bankunicode) {
                case $bankUnicodeArr['0']:
                    $tableName = DbTable::TABLE_REMITTANCE_REFUND;
                    break;
                case $bankUnicodeArr['1']:
                    $tableName = DbTable::TABLE_REMITTANCE_REFUND;
                    break;
                case $bankUnicodeArr['2']:
                    $tableName = DbTable::TABLE_RATNAKAR_REMITTANCE_REFUND;
                    break;
                case $bankUnicodeArr['3']:
                    $tableName = DbTable::TABLE_KOTAK_REMITTANCE_REFUND;
                    break;
                default :
                    $tableName = DbTable::TABLE_KOTAK_REMITTANCE_REFUND;
            }
        
            $select = $this->_db->select();
            $select->from($tableName , array('txn_code as refund_txn_code'));
            
            $select->where("remittance_request_id = ?", $rmid);
            return $this->_db->fetchRow($select);
    }
    
    public function getRemitKotakFailureRecon($param)
    {
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_FILES , array('file_name', 'comment'));
        $select->where("label = 'REMIT_KOTAK_FAILURE_RECON_FILE' AND DATE(date_created) BETWEEN '" . $param['from'] . "' AND '" . $param['to'] . "'");

        return $this->_db->fetchAll($select);
    }
    
    
    
    
    /* getBeneficiaryException() will return the Beneficiary exception for more than 1 lakh.
     * Params/Fileters :- to date and from date 
     */

    public function getBeneficiaryException($param) {

        $toDate = $param['to_date'];
        $fromDate = $param['from_date'];
        $detailsArr = array();
        if ($toDate != '' && $fromDate != '') {

            $params = array('to' => $toDate, 'from' => $fromDate);

            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $remitRequest = new Remit_Kotak_Remittancerequest();
                    $detailsArr = $remitRequest->getBeneficiaryException($params);
                    break;
                case $bankUnicodeArr['2']:
                    $remitRequest = new Remit_Ratnakar_Remittancerequest();
                    $detailsArr = $remitRequest->getBeneficiaryException($params);
                    break;
                case $bankUnicodeArr['1']:
                    $remitRequest = new Remit_Remittancerequest();
                    $detailsArr = $remitRequest->getBeneficiaryException($params);
                    break;
            }
        } 

        return $detailsArr;
    }

    /* exportBeneficiaryException() will return Beneficiary exception for more than 1 lakh
     * As params :- date will be expected.
     */

    public function exportBeneficiaryException($param) {

        $remittanceData = $this->getBeneficiaryException($param);
        $retData = array();

        if (!empty($remittanceData)) {

            foreach ($remittanceData as $key => $data) {
                $retData[$key]['remitter_name'] = $data['remitter_name'];
                $retData[$key]['remitter_mobile_number'] = $data['remitter_mobile_number'];
                $retData[$key]['remitter_address'] = $data['remitter_address'];
                $retData[$key]['beneficiary_name'] = $data['beneficiary_name'];
                $retData[$key]['total_amount'] = $data['total_amount'];
                $retData[$key]['total_count'] = $data['total_count'];
                $retData[$key]['dist_code'] = $data['dist_code'];
                $retData[$key]['dist_name'] = $data['dist_name'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['bank_account_number'] = $data['bank_account_number'];
                $retData[$key]['ifsc_code'] = $data['ifsc_code'];
                $retData[$key]['address_line1'] = $data['address_line1'];
                $retData[$key]['address_line2'] = $data['address_line2'];
                $retData[$key]['product_name'] = $data['product_name'];
                $retData[$key]['product_code'] = $data['product_code'];
                
            }
        }

        return $retData;
    }
    
    public function SaveRemittanceTxnDetails($param) {
        $ext = 'csv';
        $mobile_no = isset($param['mobile_no']) ? $param['mobile_no'] : '';
        $txn_no = isset($param['txn_no']) ? $param['txn_no'] : '';
        $from = isset($param['from']) ? $param['from'] : '';
        $to = isset($param['to']) ? $param['to'] : '';
        $bankUnicode = isset($param['bank_unicode']) ? $param['bank_unicode'] : '';
	$product_id = isset($param['product_id']) ? $param['product_id'] : '';
        $user = Zend_Auth::getInstance()->getIdentity();
        $bankObj = new Banks();
        $bank = $bankObj->getBankbyUnicode($param['bank_unicode']);
        $result = '';
        
        try{
	    
	    $paramString = 'bank_unicode='.$bankUnicode.'&date_start='.$from.'&date_end='.$to.'&mobileno='.$mobile_no.'&txnno='.$txn_no.'&product_id='.$product_id;
            
            //Disable DB Slave
            /*$this->_enableDbSlave();
            $select = $this->_db->select()
                    ->from(DbTable::TABLE_FILES, array('id', 'file_name', 'status', 'params'))
                    ->where("params = ?", $paramString)
                    ->where("bank_id = ?", $bank['id']);
            
            $result = $this->_db->fetchAll($select);
            //Disable DB Slave
            $this->_disableDbSlave();
            
            if(!empty($result)){
                foreach($result as $val){
                    if($val['status'] == 'active'){
                        return array('status' => 'processed',
                            'rs' => $result);
                    } else {
                        if($val['status'] == 'started' || $val['status'] == 'pending'){
                            return array('status' => 'in_process',
                            'rs' => $result);
                        }
                    }
                }
            }*/
       
            if(empty($result) ){
                $dataArr = array();
                
                $dataArr['bank_id'] = $bank['id'];
                $dataArr['label'] = REMITTANCE_TRANSACTION_FILE;
                $dataArr['file_name'] = '';
                $dataArr['params'] = $paramString;
                $dataArr['ops_id'] = $user->id;
                $dataArr['status'] = $param['status'];
                $dataArr['date_created'] = new Zend_Db_Expr('NOW()');

                $this->_db->insert(DbTable::TABLE_FILES, $dataArr);
                $lastId = $this->_db->lastInsertId();
                
                $file_name = 'REMITTANCE_TRANSACTION_' . $lastId . '.' . $ext;
                $this->_db->update(DbTable::TABLE_FILES, array('file_name'=> $file_name), 'id='.$lastId);
                return array('status' => 'submitted',
                            'rs' => array());
            }
        }catch (Exception $e) {
                App_Logger::log($e->getMessage(), Zend_Log::ERR);           
        }
    }
    
    public function generateRemittanceTransactionFile() {
        
        try{
            //Enable DB Slave
            $this->_enableDbSlave();
            
            $select = $this->_db->select();
            $select->from(DbTable::TABLE_FILES, array('id','status','file_name','params'));
            $select->where("label =?",REMITTANCE_TRANSACTION_FILE);
            $select->where("status =?",STATUS_PENDING);
            $criteria = $this->_db->fetchAll($select);
            
            //Disable DB Slave
            $this->_disableDbSlave(); 
                
            $i = 0;

            $columns = array(
		array(
		    'txn_date' => 'Transaction Date',
		    'sup_dist_code' => 'Super Distributor Code',
		    'sup_dist_name' => 'Super Distributor Name',
		    'dist_code' => 'Distributor Code',
		    'dist_name' => 'Distributor Name',
		    'agent_code' => 'Agent Code',
		    'agent_mobile' => 'Agent Mobile Number',
		    'agent_email' => 'Agent Email ID',
		    'agent_name' => 'Agent Name',
		    'estab_city' => 'Agent City',
		    'estab_pincode' => 'Agent Pincode',
		    'txn_type' => 'Transaction Code',
		    'amount' => 'Transaction Amount',
		    'mobile_number' => 'Customer Mobile Number',
		    'txn_code' => 'Transaction Reference Number',
		    'txnrefnum' => 'FTL Transaction ID',
		    'refund_txn_code' => 'Refund/Reversed Trx Ref Number',
		    'remit_name' => 'Remitter Name',
		    'remit_mobile_number' => 'Remitter Mobile Number',
		    'remit_email' => 'Remitter Email',
		    'remit_regn_date' => 'Remitter Registration Date',
		    'bene_name' => 'Bene Name',
		    'bene_bankname' => 'Bene Bank Name',
		    'bene_ifsccode' => 'Bene IFSC Code',
		    'txn_status' => 'Current Transaction Status',
		    'reason' => 'Reason',
		    'reason_code' => 'Reason Code',
		    'utr_no' => 'UTR No',
		    'batch_name' => 'Batch Name',
		    'transfer_mode' => 'Transfer Mode'
		));
            
            foreach($criteria as $params){
                $file = new Files();                    
                $file->setFilePermission('');
                   
                $paramsArr = explode('&', $params['params']);
                $bankUnicode = explode('=', $paramsArr[0]);
		
		$KotakBank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
		$bankKotakUnicode = $KotakBank->bank->unicode; 
	
                $this->_db->update(DbTable::TABLE_FILES ,array('status'=> STATUS_STARTED), "id=".$params['id']);
                
                $data = $this->getRemittance($params);

                $retData = array();
                $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");

                if (!empty($data)) {

                    foreach ($data as $key => $data) {

                        $retData[$key]['txn_date'] = $data['txn_date'];

                        $retData[$key]['sup_dist_code'] = $data['sup_dist_code'];
                        $retData[$key]['sup_dist_name'] = $data['sup_dist_name'];
                        $retData[$key]['dist_code'] = $data['dist_code'];
                        $retData[$key]['dist_name'] = $data['dist_name'];
                        $retData[$key]['agent_code'] = $data['agent_code'];
                        $retData[$key]['agent_mobile'] = $data['agent_mobile'];
                        $retData[$key]['agent_email'] = $data['agent_email'];
                        $retData[$key]['agent_name'] = $data['agent_name'];
                        $retData[$key]['estab_city'] = $data['estab_city'];
                        $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                        $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['txn_type']];
                        $retData[$key]['amount'] = $data['amount'];
                        $retData[$key]['mobile_number'] = $data['mobile_number'];
                        $retData[$key]['txn_code'] = $data['txn_code']; 
			$retData[$key]['txnrefnum'] = ($bankUnicode[1] == $bankKotakUnicode)?$data['txnrefnum']:'' ; 

                        if(isset($data['rmid']))
                        {
                            $refund_txn = $this->getRefundTxnRefNo($data['rmid'],$bankUnicode[1]);

                            if(!empty($refund_txn))
                            {
                                $retData[$key]['refund_txn_code'] = $refund_txn['refund_txn_code'];
                            }
                            else
                            {
                                $retData[$key]['refund_txn_code'] = '';
                            }
                        }
                        elseif(isset($data['refund_txn_code']))
                        {
                            $retData[$key]['refund_txn_code'] = $data['refund_txn_code'];
                        }
                        else
                        {
                            $retData[$key]['refund_txn_code'] = '';
                        }

                        if($data['txn_status'] == STATUS_IN_PROCESS) {
                            $txn_status  = 'In Process';
                        } else {
                            $txn_status  = ucwords($data['txn_status']);                                
                        }

                        $retData[$key]['remit_name'] = $data['remit_name'];
                        $retData[$key]['remit_mobile_number'] = $data['mobile_number'];
                        $retData[$key]['remitter_email'] = $data['remitter_email'];
                        $retData[$key]['remit_regn_date'] = $data['remit_regn_date'];
                        $retData[$key]['bene_name'] = $data['bene_name'];
                        $retData[$key]['bene_bankname'] = $data['bene_bankname'];
                        $retData[$key]['bene_ifsccode'] = $data['bene_ifsccode'];
                        $retData[$key]['txn_status'] = $txn_status;
                        $reason = explode(')', $data['final_response']);
                        $retData[$key]['reason'] = ltrim($reason[1]);
                        $retData[$key]['reason_code'] = str_replace('(', '', $reason[0]);
                        $retData[$key]['utr_no'] = isset($data['utr']) ? $data['utr'] : '';
                        $retData[$key]['batch_name'] = $data['batch_name'];
			if(isset($data['flag'])){
                    		if($data['flag']==2){
                    			$retData[$key]['transfer_mode'] = "IMPS";
                    		}else{
                    			$retData[$key]['transfer_mode'] = "NEFT";
                    		}
                    	}

                    }
                }
                $resultArr = array_merge($columns, $retData);

                // Save File 
                $file->setBatch($resultArr, SEPARATOR_COMMA);
                $file->setFilepath(UPLOAD_PATH_REMITTANCE_TRANSACTION_REPORTS);
                $file->setFilename($params['file_name']);
                $file->generate(TRUE);

                $this->_db->update(DbTable::TABLE_FILES ,array('status'=> STATUS_ACTIVE), "id=".$params['id']);
                $i++;
            }
            return $i;
        }catch (Exception $e) {
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
        }
    }
    
     public function getRemittanceDetails($param) {

       
        $detailsArr = array();
        if (!empty($param)) {

            $bankUnicodeArr = Util::bankUnicodesArray();

            if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
                $param['bank_unicode'] = $bankUnicodeArr['1'];
            }

            switch ($param['bank_unicode']) {
                case $bankUnicodeArr['3']:
                    $objRemitter = new Remit_Kotak_Remittancerequest();
                    $detailsArr = $objRemitter->getKotakRemittance($param);
                    break;
                case $bankUnicodeArr['2']:
                    $objRemitter = new Remit_Ratnakar_Remittancerequest();
                    $detailsArr = $objRemitter->getRatnakarRemittance($param);
                    break;
                case $bankUnicodeArr['1']:
                    $objRemitter = new Remit_Boi_Remitter();
                    $detailsArr = $objRemitter->getBoiRemittance($param);
                    break;
            }
        } // date check if
        return $detailsArr;
    }
    
    public function exportRemittanceDetails($param) {
       
        $data = $this->getRemittanceDetails($param);
        
        $retData = array();
        $TXN_TYPE_LABELS = Zend_Registry::get("TXN_TYPE_LABELS");
        $agentUser = new AgentUser(); 
	$bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
	$bankBoiUnicode = $bankBoi->bank->unicode;
	$RatnakarBank = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
	$bankRatnakarUnicode = $RatnakarBank->bank->unicode;
	$KotakBank = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
	$bankKotakUnicode = $KotakBank->bank->unicode; 
        if (!empty($data)) {

            foreach ($data as $key => $data) {

                $retData[$key]['txn_date'] = $data['txn_date'];
                
                $agentUser = new AgentUser();
                $agentType = $agentUser->getAgentCodeName($data['agent_user_type'], $data['agent_id']);

                if(!empty($agentType))
                {
                    $retData[$key] = array_merge($retData[$key], $agentType);
                }

                $retData[$key]['agent_code'] = $data['agent_code'];
                $retData[$key]['agent_mobile'] = $data['agent_mobile'];
                $retData[$key]['agent_email'] = $data['agent_email'];
                $retData[$key]['agent_name'] = $data['agent_name'];
                $retData[$key]['estab_city'] = $data['estab_city'];
                $retData[$key]['estab_pincode'] = $data['estab_pincode'];
                $retData[$key]['txn_type'] = $TXN_TYPE_LABELS[$data['txn_type']];
                $retData[$key]['amount'] = $data['amount'];
                $retData[$key]['mobile_number'] = $data['mobile_number'];
                $retData[$key]['txn_code'] = $data['txn_code'];
		$retData[$key]['txnrefnum'] = ($param['bank_unicode'] == $bankKotakUnicode)?$data['txnrefnum']:'' ; 
                //$retData[$key]['batch_name'] = ($param['bank_unicode'] == $bankBoiUnicode) ? $data['batch_name'] : '';
                
                if(isset($data['rmid']))
                {
                    $refund_txn = $this->getRefundTxnRefNo($data['rmid'],$bankunicode);

                    if(!empty($refund_txn))
                    {
                        $retData[$key]['refund_txn_code'] = $refund_txn['refund_txn_code'];
                    }
                    else
                    {
                        $retData[$key]['refund_txn_code'] = '';
                    }
                }
                elseif(isset($data['refund_txn_code']))
                {
                    $retData[$key]['refund_txn_code'] = $data['refund_txn_code'];
                }
                else
                {
                    $retData[$key]['refund_txn_code'] = '';
                }
                
                if($data['txn_status'] == STATUS_IN_PROCESS) {
                                $txn_status  = 'In Process';
                            } else {
                                $txn_status  = ucwords($data['txn_status']);                                
                    }
               // $retData[$key]['utr_no'] = '';
                $retData[$key]['remit_name'] = $data['remit_name'];
                $retData[$key]['remit_mobile_number'] = $data['mobile_number'];
                $retData[$key]['remitter_email'] = $data['remitter_email'];
                $retData[$key]['remit_regn_date'] = $data['remit_regn_date'];
                $retData[$key]['bene_name'] = $data['bene_name'];
                $retData[$key]['bene_bankname'] = $data['bene_bankname'];
                $retData[$key]['bene_ifsccode'] = $data['bene_ifsccode'];
                $retData[$key]['txn_status'] = $txn_status;
                $reason = explode(')', $data['final_response']);
                $retData[$key]['reason'] = ltrim($reason[1]);
                $retData[$key]['reason_code'] = str_replace('(', '', $reason[0]);
                $retData[$key]['utr_no'] = ( ($param['bank_unicode'] == $bankRatnakarUnicode) ) ? $data['utr'] : '';
                $retData[$key]['batch_name'] = $data['batch_name'];
            }
           
        }
        return $retData;
    }


    public function getRemitRecon($param)
    {
        $bankUnicodeArr = Util::bankUnicodesArray();

        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['3']:
                $label = KOTAK_REMIT_TXN_RECON_FILE;
                break;
            case $bankUnicodeArr['2']:
                $label = RAT_REMIT_TXN_RECON_FILE;
                break;            
        } 
        
        $select = $this->_db->select();
        $select->from(DbTable::TABLE_FILES , array('file_name'));
        $select->where("label = '".$label."' AND DATE(date_created) BETWEEN '" . $param['from'] . "' AND '" . $param['to'] . "'");

        return $this->_db->fetchAll($select);
    }
}
