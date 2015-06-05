<?php
/**
 * Default entry point in the application
 *
 * @package frontend_controllers
 * @copyright company
 */

class IndexController extends App_Bank_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init()
    {
        //App_Logger::log("Testing Error Message", Zend_Log::ERR);
        // init the parent
       /* $ecsApi = new App_Socket_ECS_Corp_Transaction();
        $val['transactionId'] = rand('111111111111','999999999999');
        $val['crn'] = '4780745100001187';
        $val['amount'] = '1234';
        $val['date_transaction'] = '';
        $resp = $ecsApi->reversalMACardLoad($val);        
        print $resp;exit('asfsdf');
        */
        
//        $userObj = new Mvc_Axis_CardholderUser();
//        $ecs = new App_Api_ECS();
//        $ecs->cardholderRegistration($userObj);
//        exit("testing Cardholder Registration");
        //echo urlencode(Util::ssl_encrypt('/index/test'));exit;
        //echo Util::ssl_decrypt('efe17010058074ea3dc0bcc41a2ab83a', 'U2FsdGVkX1+7vxcSOrJ8m8phsuRVB2Dz4R4SQcO14Qs=');
        //echo '<br />';
        //exit;
//        $a = base64_encode('/index/test');
//        $a = base64_encode('/profile/login');
//        print $a;exit;
//        $m = new App\Messaging\MVC\Axis\Agent();
//        $m->authCode(
//                array(
//                        'auth_code' => '123456',
//                        'mobile' => '9899195914',
//                 )
//        );
//        exit;            
        
        //echo Util::decryptURL(urldecode('7OGmf69Zal701Hfpy3x76Io1vaymE1ig1Irbvsud%2FmzBgouB%2Fj3n7%2FKVqxm2W%2B58d3tUm1HRzfEPNM6VQXAAG4Hkt4NJ%2Bc60jE9ZfTtuA7Jon7KlvOQCWpXug6QI%2BNaB'));
        //exit;
        parent::init();
    }
    
    /**
     * Controller's entry point
     *
     * @access public
     * @return void
     */
    public function indexAction()
    {
        $this->_redirect($this->formatURL('/profile/login/'));
//       $user = Zend_Auth::getInstance()->getIdentity();
//       if(isset($user->id)) {
//           $this->_redirect($this->formatURL('/profile/index'));
//           exit;
//       }
//        $this->_helper->layout()->setLayout('withoutlogin');        
    }

    public function getCardholderArray() {
        
        
        //$paramArray['cardNumber'] = '2222220000000010';
        $paramArray['cardNumber'] = '3333330000001902';
        //$paramArray['channel'] = 'IVR';
        //$paramArray['componentAuthKey'] = 'transerv';
        //$paramArray['componentId'] = '';
        //$paramArray['expiryDate'] = '';
        //$paramArray['ip'] = '10.10.10.144';
        //$paramArray['passCodeFlag'] = 'N';
        //$paramArray['passCodeValue'] = '';
        //$paramArray['password'] = '';
        //$paramArray['requestDateTime'] = '';
        //$paramArray['serviceCode'] = '205';
        //$paramArray['sessionKey'] = '';
        //$paramArray['terminalType'] = '';
        //$paramArray['txnPassFlag'] = '';
        //$paramArray['txnPassword'] = '';
        //$paramArray['userId'] = 'transerv';
        $paramArray['address1'] = '989/78';
        $paramArray['address2'] = '';
        $paramArray['address3'] = '';
        $paramArray['address4'] = 'MAH';
        $paramArray['bankcode'] = '';
        $paramArray['birthcity'] = '';
        $paramArray['birthcountry'] = '';
        $paramArray['birthdate'] = '03101986';
        $paramArray['citycode'] = 'THN';
        $paramArray['countrycode'] = '356';
        $paramArray['emailid'] = '';
        $paramArray['embossedname'] = 'Vikram Singh';
        $paramArray['employer'] = '';
        $paramArray['employmentstatus'] = '';
        $paramArray['familyname'] = 'Singh';
        $paramArray['firstname'] = 'Vikram';
        $paramArray['gender'] = 'M';
        $paramArray['legalid'] = '';
//        $paramArray['legalidentificationtype'] = '';
//        $paramArray['mailingaddress1'] = '';
//        $paramArray['mailingaddress2'] = '';
//        $paramArray['mailingaddress3'] = '';
//        $paramArray['mailingaddress4'] = '';
//        $paramArray['mailingcitycode'] = '';
//        $paramArray['mailingcountrycode'] = '';
//        $paramArray['mailingzipcode'] = '';
//        $paramArray['maritalstatus'] = '';
//        $paramArray['middlename'] = '';
//        $paramArray['middlename2'] = '';
        $paramArray['mothersmaidenname'] = 'Singh';
//        $paramArray['nationalitycode'] = '';
//        $paramArray['officeaddress1'] = '';
//        $paramArray['officeaddress2'] = '';
//        $paramArray['officeaddress3'] = '';
//        $paramArray['officeaddress4'] = '';
//        $paramArray['officecitycode'] = '';
//        $paramArray['officecountrycode'] = '';
//        $paramArray['officemobile'] = '';
//        $paramArray['officephone1'] = '';
//        $paramArray['officephone2'] = '';
//        $paramArray['officezipcode'] = '';
//        $paramArray['permanentmobile'] = '';
//        $paramArray['permanentphone1'] = '';
//        $paramArray['permanentphone2'] = '';
//        $paramArray['phonealternate'] = '';
//        $paramArray['phonehome'] = '';
        $paramArray['phonemobile'] = '9899195914';
//        $paramArray['preferredmailingaddress'] = '';
//        $paramArray['priorityrequest'] = '';
//        $paramArray['remarks'] = '';
//        $paramArray['residencestatus'] = '';
//        $paramArray['title'] = '';
//        $paramArray['userdefinedfield3'] = '';
//        $paramArray['userdefinedfield4'] = '';
//        $paramArray['userdefinedfield5'] = '';
//        $paramArray['userdefinedfield6'] = '';
//        $paramArray['userdefinedfield7'] = '';
//        $paramArray['userdefinedfield8'] = '';
//        $paramArray['userdefinedfield9'] = '';
//        $paramArray['userid'] = 'transerv';
        $paramArray['zipcode'] = '411052';
//        //$paramArray['cardNumber'] = '2222220000000010';
//        $paramArray['cardNumber'] = '3333330000000995';
//        $paramArray['channel'] = 'IVR';
//        $paramArray['componentAuthKey'] = 'transerv';
//        $paramArray['componentId'] = '';
//        $paramArray['expiryDate'] = '';
//        $paramArray['ip'] = '10.10.10.144';
//        $paramArray['passCodeFlag'] = 'N';
//        $paramArray['passCodeValue'] = '';
//        $paramArray['password'] = '';
//        $paramArray['requestDateTime'] = '';
//        $paramArray['serviceCode'] = '205';
//        //$paramArray['sessionKey'] = '';
//        $paramArray['terminalType'] = '';
//        $paramArray['txnPassFlag'] = '';
//        $paramArray['txnPassword'] = '';
//        $paramArray['userId'] = 'transerv';
//        $paramArray['address1'] = '989/78';
//        $paramArray['address2'] = '';
//        $paramArray['address3'] = '';
//        $paramArray['address4'] = 'MAH';
//        $paramArray['bankcode'] = '';
//        $paramArray['birthcity'] = '';
//        $paramArray['birthcountry'] = '';
//        $paramArray['birthdate'] = '03101986';
//        $paramArray['citycode'] = 'THN';
//        $paramArray['countrycode'] = '356';
//        $paramArray['emailid'] = '';
//        $paramArray['embossedname'] = 'Vikram Singh';
//        $paramArray['employer'] = '';
//        $paramArray['employmentstatus'] = '';
//        $paramArray['familyname'] = 'Singh';
//        $paramArray['firstname'] = 'Vikram';
//        $paramArray['gender'] = 'M';
//        $paramArray['legalid'] = '';
//        $paramArray['legalidentificationtype'] = '';
//        $paramArray['mailingaddress1'] = '';
//        $paramArray['mailingaddress2'] = '';
//        $paramArray['mailingaddress3'] = '';
//        $paramArray['mailingaddress4'] = '';
//        $paramArray['mailingcitycode'] = '';
//        $paramArray['mailingcountrycode'] = '';
//        $paramArray['mailingzipcode'] = '';
//        $paramArray['maritalstatus'] = '';
//        $paramArray['middlename'] = '';
//        $paramArray['middlename2'] = '';
//        $paramArray['mothersmaidenname'] = 'Singh';
//        $paramArray['nationalitycode'] = '';
//        $paramArray['officeaddress1'] = '';
//        $paramArray['officeaddress2'] = '';
//        $paramArray['officeaddress3'] = '';
//        $paramArray['officeaddress4'] = '';
//        $paramArray['officecitycode'] = '';
//        $paramArray['officecountrycode'] = '';
//        $paramArray['officemobile'] = '';
//        $paramArray['officephone1'] = '';
//        $paramArray['officephone2'] = '';
//        $paramArray['officezipcode'] = '';
//        $paramArray['permanentmobile'] = '';
//        $paramArray['permanentphone1'] = '';
//        $paramArray['permanentphone2'] = '';
//        $paramArray['phonealternate'] = '';
//        $paramArray['phonehome'] = '';
//        $paramArray['phonemobile'] = '';
//        $paramArray['preferredmailingaddress'] = '';
//        $paramArray['priorityrequest'] = '';
//        $paramArray['remarks'] = '';
//        $paramArray['residencestatus'] = '';
//        $paramArray['title'] = '';
//        $paramArray['userdefinedfield3'] = '';
//        $paramArray['userdefinedfield4'] = '';
//        $paramArray['userdefinedfield5'] = '';
//        $paramArray['userdefinedfield6'] = '';
//        $paramArray['userdefinedfield7'] = '';
//        $paramArray['userdefinedfield8'] = '';
//        $paramArray['userdefinedfield9'] = '';
//        $paramArray['userid'] = 'transerv';
//        $paramArray['zipcode'] = '411052';
        return $paramArray;        
    }
    
    
    public function testAction() {
        
        
         
$postdata = http_build_query(
    array(
        'SPCode' => '10022',
        'SPOrderNo' => '12345678',
        'Amount' => '3000.00',
        'SuccessURL' => 'http://qa-partner.shmart.in/trfr-success',
        'FailureURL' => 'http://qa-partner.shmart.in/trfr-fail',
        'ConfirmURL' => 'http://qa-partner.shmart.in/trfr-confirm',
        'ProductCode' => 'TRV0001',
        'erupeeCommission' => '',
        'CustomerName' => 'demo123',
        'CustomerMobile' => '919502380380',
        'CustomerEmail' => 'demo123@demo.com',
        'OrderDetails' => 'Remittance Instruction'
    )
);
$opts = array('http' =>
    array(
        'method'  => 'POST',
        'content' => $postdata
    )
);

//$urltopost = 'http://www.erupee.in/v2/retailer/login.aspx';
$urltopost = 'http://www.erupee.in/V2/serviceprovider/PaymentAuthentication.aspx';

$context  = stream_context_create($opts);
$returndata = @file_get_contents('http://www.erupee.in/V2/serviceprovider/PaymentAuthentication.aspx', false, $context);


//$ch = curl_init ($urltopost);
//curl_setopt ($ch, CURLOPT_POST, true);
//curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
//curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
//$returndata = curl_exec ($ch);
//$errordata = curl_error($ch);
//curl_close ($ch);


print $returndata;
        exit();
      
      
        $msg = '';
        try {
            $cardholderArray = $this->getCardholderArray();
            $ecsApi = new App_Api_ECS_Corp_Ratnakar();
            $resp = $ecsApi->mediAssistCardholderRegistration($cardholderArray);
            if ($resp == false) {
                $msg = $ecsApi->getError();
            }
        } catch (App_Exception $e) {
            $resp = false;
            $msg = $e->getMessage();
        } catch (Exception $e) {
            $resp = false;
            $msg = $e->getMessage();
        }
        if ($resp == true) {
            //On Success
            print '<pre>';
            print_r($ecsApi->getLastResponse());
        } else {
            //On Failure
            print $error = "Error Message : " . $msg;
        }
        exit;

        //print 'here';exit;
        /*
        print '<br /><br />';
        print 'Value : '.$_GET['a'];
        print '<br /><br />';
        print 'Value : '.  urldecode($_GET['a']);
        print '<br /><br />';
        print Util::decrypt(urldecode($_GET['a']));
        print '<br /><br />';
        print Util::decrypt($_GET['a']);
        print '<br /><br />';
        print $a = urlencode(Util::encrypt("/profile/login"));
        print '<br /><br />';
        print '<a href="/index/test/?a='.$a.'">Click here</a>';
        exit;
        //print Util::encryptURL('/index/test/a/acbadfadf/b/asdfdf/c/adfsfsdf');//exit;
        //print Util::encryptURL('/index/test?a=acbadfadf&b=asdfdf&c=adfsfsdf');//exit;
        //print '<a href="'.Util::encryptURL('/profile/login?a=acbadfadf&b=asdfdf&c=adfsfsdf').'" >click here</a>';//exit;
        print '<a href="'.Util::formatURL('/index/test?a=acbadfadf&b=asdfdf&c=adfsfsdf').'" >click here</a>';//exit;
        print '<a href="'.Util::formatURL('http://agent.shmart.local/profile/').'" >click here</a>';//exit;
       $param = $this->_getAllParams();
       print '<pre>';print_r($param);exit;
        */
    $iso8583	= new App_ISO_ISO8583();          
          //$iso = '0097ISO01600007502007238E00108C0800016333333000000144987000000000003500002051731402580591731400205020559993560641995300008760001230010001300100011234567356';
          //$iso = '0097ISO01600007502007238E00108C0800016333333000000000087000000000002900002051753067628851753060205020559993560641995300008760000930010001300100011234567356';
          //$iso = '0043ISO0060000750800822000000000000004000000000000000205171002553038001';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    //$iso ='0053ISO0060000750810200000020000000400000000000000051306275211144400301';
    //$iso ='0033ISO0060000750800822000000000000004000000000000000525042535111224001';
    //$iso ='0033ISO0060000750800822000000000000004000000000000000525042535111224001';
    
    
    //$iso = $this->readfileiso();
    //print '<br /><br />';
    //$iso = $this->convert2ascii($iso);
    print '<br /><br />';
    //$iso ='00B9ISO0160000750121723E64012AE080003634313939353318455308870000000000000100000524103359000068053313052417050524596984002106123457.4199531845531E+16ACCEPTOR  AC00IRER NAME            CI?TY';
    //$iso ='00E7ISO0160000750120723e64012ae08000313634313939353318455308870000000000000100000524103359000068053313052417050524596984002106123456374199531845530887=1705000100000009999931441000006812TERMID01CARD ACCEPTOR ACQUIRER NAME CITY NAME US840';
    //$iso = strtoupper($iso);
    //$iso = '0045ISO016000075081082200000020000000400000000000000012303292113203500001';
    //$iso = '0033ISO0060000750800822000000000000004000000000000000529120819111135001';
    //$iso = '0055ISO0060000750810822000000200000004000000000000000400000000000000052912004811113000001';
    //$iso = '0055ISO0060000750810822000000200000004000000000000000400000000000000052912081911113500001';
    $iso = '0045ISO006000075081082200000020000000400000000000000052912402311115200001';
    //printf('%b','0045ISO016000075');exit;
    
    //$iso8583->addMTI('')
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    print '<br /><br /><br />';
    
    
    
    $iso8583->addMTI('0810');
    $iso8583->addData(39, '00');
    
print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    print '<br /><br /><br />';
    
    print $iso8583->getISOwithHeaders();
    exit;
    
    $iso8583	= new App_ISO_ISO8583();          
          $iso = '00D1ISO0160000750420F238E00108C08000000000400000000016333333000000144987000000000003500002051732012580591731400205020559993560641995300008760001230010001300100011234567356020025805902051731400000041995300000000000';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    
    //printf('%b','0045ISO016000075');exit;
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    print '<br /><br /><br />';
    
    $iso8583	= new App_ISO_ISO8583();          
          $iso = '0054ISO0160000750430723040010EC081001633333300000014498700000000000350000205173201258059';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    
    //printf('%b','0045ISO016000075');exit;
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    print '<br /><br /><br />';
    print 'Approved Reversal';
    print '<br /><br /><br />';
    
    
    
    $iso8583	= new App_ISO_ISO8583();          
          $iso = '00D1ISO0160000750420F238E00108C08000000000400000000016333333000000135787000000000000015001311414066704251400350131013159993560641995300008760000530010001300100011234567356020067042501311400350000041995300000000000';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    
    //printf('%b','0045ISO016000075');exit;
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    print '<br /><br /><br />';
    $iso8583	= new App_ISO_ISO8583();          
          $iso = '00ABISO0160000750430722000010EC08100163333330000001357870000000000000150013114140667042506419953000087600005X542750000000000300100011234567356030120067042513013102295506419953';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    
    //printf('%b','0045ISO016000075');exit;
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';
    print_r($iso8583->getBitmap());
    print '<br /><br /><br />';
    exit;
    /*try {
        $mvc = new App_Api_MVC_Transactions();
        $flg = $mvc->sendDownloadLink('9899195914');
        } catch (Exception $e) {
            print $e->getMessage();exit;
        }
        //echo "<pre>";print_r($flg);
        if($flg) {
            echo "<br />Download link send successfully<br /><br />";
        } else {
            //print '<br />';
            print 'ERROR:' .$mvc->getError() . '<br />';
            echo "<br />Download link send Failed <br />";
            //echo $ecs->getError();

        }*/
        //exit        
      /*  $ob = new stdClass();
        $a = new App_ApiServer_Exchange_Services($ob);
        $obj='<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://qa-api.shmart.in/services/exchange"><SOAP-ENV:Header/><SOAP-ENV:Body><sas:AuthenticationRequest><Message id="123456"><SessionID>820121219171917</SessionID><PAN>4199530000000001</PAN><Amount>10000</Amount><ExpiryDate>0701</ExpiryDate><OTP>1234</OTP></Message></sas:AuthenticationRequest></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        $resp = $a->AuthenticationRequest($obj);        
               echo "<pre>";print_r($resp);
                exit;*/
     //print dechex(67);exit;
     // 
/*
     //Stop Card
        $param['amount'] = '500';
        $param['stan'] = '1234';
        $param['agentId'] = '12345678';
        $param['crn'] = '3333330000000201';
        $ecsApi = new App_Api_ECS_Transactions();
        //print $ecsApi->firstCardLoad($param);
        print $ecsApi->stopCard($param);
        exit;
 */ 
 
/*        
     //CARD LOAD
        $param['amount'] = '500';
        $param['stan'] = '1234';
        $param['agentId'] = '12345678';
        $param['crn'] = '3333330000000201';
        $ecsApi = new App_Socket_ECS_Transaction();
        //print $ecsApi->firstCardLoad($param);
        print $ecsApi->generateFirstCardLoadISO($param);
        exit;
*/
        
 /*    //CARD LOAD
        $param['amount'] = '500';
        $param['stan'] = '1234';
        $param['agentId'] = '12345678';
        $param['crn'] = '3333330000000201';
            $ecsApi = new App_Socket_ECS_Transaction();
            //print $ecsApi->firstCardLoad($param);
            print $ecsApi->generateFirstCardLoadISO($param);
            exit;
   */      
     /*   
        try {
            
            //Balance Inquiry
            //$cardholderArray = $this->getCardholderArray();
            $cardholderArray['cardNumber'] = '3333330000000987';

            $ecsApi = new App_Api_ECS_Transactions();
            //$resp = $ecsApi->balanceInquiry($cardholderArray);
            $resp = $ecsApi->transactionHistory($cardholderArray);
            if($resp == true) {
                //On Success
                //print "Request :" .$ecsApi->getLastRequest();
                print '<pre>';
               print_r($ecsApi->getLastResponse());
               //var_dump($ecsApi->getLastResponse());
            } else {
                //Fail
                print $error = "Error Message : " . $ecsApi->getError();
                //print '<br />';
                //print_r($ecsApi->getLastResponse());
            }
        } catch( Exception $e ) {
            $resp = false;
            $error = $e->getMessage();
        }
*/
        
        //Transaction History
        /*
            $cardholderArray['cardNumber'] = '3333330000000987';
            /*$cardholderArray['fetchFlag'] = '1';
            $cardholderArray['fromDate'] = '';
            $cardholderArray['noOfTransactions'] = '10';
            $cardholderArray['toDate'] = '';
            */
          /*  
            $cardholderArray['fetchFlag'] = '0';
            $cardholderArray['fromDate'] = '01012013';
            $cardholderArray['noOfTransactions'] = '1';
            $cardholderArray['toDate'] = '01052013';
            $ecsApi = new App_Api_ECS_Transactions();
            //$resp = $ecsApi->balanceInquiry($cardholderArray);
            $resp = $ecsApi->transactionHistory($cardholderArray);
            if($resp == true) {
                //On Success
                //print "Request :" .$ecsApi->getLastRequest();
                print '<pre>';
               print_r($ecsApi->getLastResponse());
               //var_dump($ecsApi->getLastResponse());
            } else {
                //Fail
                print $error = "Error Message : " . $ecsApi->getError();
                //print '<br />';
                //print_r($ecsApi->getLastResponse());
            }
    
        
        
        echo "<pre>";print_r($resp);
        //exit('END');            

            */
            
            $cardholderArray = $this->getCardholderArray();
            $ecsApi = new App_Api_ECS_Transactions();
            $resp = $ecsApi->cardholderRegistration($cardholderArray);
            if($resp == true) {
                //On Success
                //print "Request :" .$ecsApi->getLastRequest();
                print '<pre>';
               print_r($ecsApi->getLastResponse());
               //var_dump($ecsApi->getLastResponse());
            } else {
                //Fail
                print $error = "Error Message : " . $ecsApi->getError();
                //print '<br />';
                //print_r($ecsApi->getLastResponse());
            }
            exit;
            /*
        } catch( Exception $e ) {
            $resp = false;
            $error = $e->getMessage();
        }
        
        echo "<pre>";print_r($resp);
        exit('END');
        */
        /*
        try { 
        //$client = new Zend_Soap_Client("http://14.140.42.101:8991/WSDL", array('compression' => SOAP_COMPRESSION_ACCEPT));
        //$client = new Zend_Soap_Client(null, array(
        $client = new SoapClient(null, array(
                'location' => 'http://14.140.42.101:8991/WSDL',
                'uri'       => 'http://webservice.epms.com/',
        ));
        //$client->setSoapVersion(SOAP_1_1);
        $array = array(
            'cardNumber'    => '',
            'channel'       => 'IVR',
            'componentAuthKey'  => '',
            'componentId'   => '',
            'expiryDate'    => '',
            'ip'            => '10.10.10.144',
            'passCodeFlag'  => 'N',
            'passCodeValue' => '',
            'password'      => '',
            'requestDateTime'   => '',
            'serviceCode'   => '103',
            'sessionKey'    => 'ABCD',
            'terminalType'  => '',
            'txnPassFlag'   => '',
            'txnPassword'   => '',
            'userId'        => 'ubpuser'
        );
        $obj = new stdClass();
$obj->cardNumber    = '';
$obj->channel      = 'IVR';
$obj->componentAuthKey = '';
$obj->componentId  = '';
$obj->expiryDate    = '';
$obj->ip = '10.10.10.144';
$obj->passCodeFlag = 'N';
$obj->passCodeValue= '';
$obj->password     = '';
$obj->requestDateTime  = '';
$obj->serviceCode  = '103';
$obj->sessionKey    = 'ABCD';
$obj->terminalType = '';
$obj->txnPassFlag  = '';
$obj->txnPassword  = '';
$obj->userId       = 'ubpuser';
        

$obj2 = new stdClass();
$obj2->ID = '123123123';
$obj2->SessionKey = 'asdfasdfasdf12312';
$obj2->Channel = '123123123';

$obj3 = new stdClass();
$obj3->cardNumber = '3337770000000204';
$obj3->channel = 'IVR';
$obj3->componentAuthKey = '';
$obj3->componentId = '';
$obj3->expiryDate = '1704';
$obj3->ip = '10.10.10.144';
$obj3->passCodeFlag = 'N';
$obj3->passCodeValue = '';
$obj3->password = '';
$obj3->requestDateTime = '';
$obj3->serviceCode = '206';
$obj3->sessionKey = 'ABCD';
$obj3->terminalType = '';
$obj3->txnPassFlag = 'N';
$obj3->txnPassword = '';
$obj3->userId = 'ubpuser';
$obj3->reasonCode = '14';

$obj4 = new stdClass();
$obj4->cardNumber = '';
$obj4->channel = 'IVR';
$obj4->componentAuthKey = 'ubpuser';
$obj4->componentId = '';
$obj4->expiryDate = '';
$obj4->ip = '10.10.10.144';
$obj4->passCodeFlag = '';
$obj4->passCodeValue = '';
$obj4->password = '';
$obj4->requestDateTime = '';
$obj4->serviceCode = '101';
$obj4->sessionKey = '';
$obj4->terminalType = '';
$obj4->txnPassFlag = '';
$obj4->txnPassword = '';
$obj4->userId = 'ubpuser';
$obj4->inOut = '';
     
$arr = new SoapParam($obj, 'arg0');
$arr3 = new SoapParam($obj, 'arg0');
$arr4 = new SoapParam($obj4, 'arg0');
       // $arr2 = new SoapParam($obj2, 'arg0');
       //$client->callStopCard($arr3);
       //$client->callEchoTest($arr);
       //$client->callLogin($arr2);
        //$client->
echo '<pre>';print_r($arr4);
$client->callWebServiceLogin($arr4);
        } catch (Exception $e) {
            echo "<pre>";
            print_r($e);
        }
        
        print 'Request Header :' . $client->__getLastRequestHeaders() . '<br />';
        print 'Request :' .htmlentities($client->__getLastRequest()) . '<br />';
        print '<br /><br />';
        print 'Response Header :' .$client->__getLastResponseHeaders() . '<br />';
        print 'Response :' . htmlentities($client->__getLastResponse()) . '<br />';
        exit;
        
        */
//        print 'Request Header :' . $client->getLastRequestHeaders() . '<br />';
//        print 'Request :' .htmlentities($client->getLastRequest()) . '<br />';
//        print '<br /><br />';
//        print 'Response Header :' .$client->getLastResponseHeaders() . '<br />';
//        print 'Response :' . htmlentities($client->getLastResponse()) . '<br />';
//        exit;
      
//        $cl = new App_Socket_ECS_Authentificator();
//        print $cl->initSession();
//        exit;
        
/*        

    $iso8583	= new App_ISO_ISO8583();     

    
//    $iso = '0069ISO006000075081082200000020000000400000000000000062307253310103300001';
//        print substr($iso, 0, 4) . "<br />";
//        print substr($iso, 4, 3) . "<br />";
//        print substr($iso, 8, 9) . "<br />";
//        print substr($iso, 17);
//exit;    
    //$iso = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    //$iso = '0045ISO016000075081082200000020000000400000000000000121906492';
    //$iso = '008CISO01600007502107230E0010E808000163333330000000185870000000000000200010914561900999114561901095999356064199530000000051133073072730010001356';
      //$iso = '0045ISO016000075081082200000020000000400000000000000011108092287240000001';
      //$iso = '00AAISO0160000750200F238E00108C08000040000000000000016333333000000031887000000000003370001111732379673601732370111011159993560641995333357401209600000044300100011234567356001';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000003188700000000000337000111173237967360173237130201115999356064199533335740120961400000044356';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000003348700000000000337000111180007360890180007130201115999356064199532475686408851400000044356';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000003348700000000000337000111181052173479181052130201115999356064199535710167895931400000044356';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000010278700000000000450000111183712977868183712130201115999356064199538855330707261400000044356';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000010278700000000000450000111185356885940185356130201115999356064199538247702647831400000044356';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000010278700000000000250000112153046415781153046130201125999356064199531987379881761400000044356';
      //$iso = '00CEISO0160000750420F238E00108C08000000000400000000013332324132412387000000000012210001131549265589951542440112011259993560641995385333977597800000000300100011234567356020055899501121542440000041995300000000000';
      //$iso = '008AISO01600007502107238E0010A8080001633333300000011348700000000001000000115202706867889202706130201155999356064199535896439012781400000044356';
      //$iso = '00CDISO0160000750420F238E00108C0800004000040000000001333232413241238700000000000010000117135615865515135611011759993560641995300001312313400000000300100011234567356001021086551501171356110000041995300000000000';
      //$iso = '00C7ISO0160000750420F238E00108C080000000004000000000061231238700000000000200000113154428558995154244011201125999356064199538533397759780000000030010001123456735602005589950112154244000004199530000000000000';
      //$iso = '00D4ISO0160000750420F238E00108C08000040000400000000016333333000000113487000000000001210001211743407111201743390121012159993560641995300008765432100000000300100011234567356001020071112001211743390000041995300000000000';


      //$iso = '00D4ISO0160000750420F238E00108C08000040000400000000016333333000000113487000000000001210001211743407111201743390121012159993560641995300008765432100000000300100011234567356001020071112001211743390000041995300000000000';
      //$iso = '00AAISO0160000750200F238E00108C08000040000000000000016333333000000124187000000000002000001221526468481811526460122012259993560641995300008765432100000000300100011234567356001';
      //$iso = '00D1ISO0160000750420F238E00108C08000000000400000000016333333000000124187000000000002000001221534568481811526460122012259993560641995300008765432130010001300100011234567356020084818101221526460000041995300000000000';
      //$iso = '00AAISO0160000750200F238E00108C08000040000000000000016333333000000124187000000000002500001221540388194061540380122012259993560641995300008765411100000000300100011234567356001';
      //$iso = '00D1ISO0160000750420F238E00108C08000000000400000000016333333000000124187000000000002500001221541348194061540380122012259993560641995300008765411130010001300100011234567356020081940601221540380000041995300000000000';
      //$iso = '00ABISO0160000751430722000010EC08100163333330000001241870000000000025000012215413481940606419953000087654111X834450000000000300100011234567356030120081940613012204095806419953';
      $iso = '00AAISO0160000750200F238E00108C08000040000000000000016333333000000115987000000000005000001221928121768821928120122012259993560641995330596220709500000044300100011234567356001';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301

    $loadISO = '00AAISO0160000750200F238E00108C08000040000000000000016333333000000113487000000000002000001211659096122231659090121012159993560641995300008765432100000000300100011234567356001';
    $param = array(
        'crn' => '3333330000001134',
        'amount' => '200',
        //'transactionTime' => '',
    );
    $ecs = new App_Socket_ECS_Transaction();
    $iso = $ecs->generateCardLoadReversalISO($loadISO,$param);
      
    //printf('%b','0045ISO016000075');exit;
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'.$iso8583->getMTI() . '<br />';
    print_r($iso8583->getData());
    print '<br /><br /><br />';

    

/*    $trans = new App_Socket_ECS_Transaction();
    $param = array(
        'crn'   => '3333330000001241',
        'amount' => '25000'
    );
    print $trans->generateCardLoadReversalISO($iso, $param);*/
/*        
    exit;
    
    
    //Card Load
    
    $iso8583->addMTI('0200');
    
    //$iso8583->addData(7, '0904072533');

    //$iso8583->addData(11, '101033');
    $iso8583->addData(70, '001');
    //Primary Bitmap
    $iso8583->addData(1, 'B238800128E19018');
    //Card Number
    $iso8583->addData(2, '4444111111111111');
    //Processing Code
    $iso8583->addData(3, '870000');
    
    //Transaction Amount
    $iso8583->addData(4, '000000050000');
    
    //Transaction Date/Time
    $iso8583->addData(7, date("mdHis"));
    
    //System Trace Audit Number
    $iso8583->addData(11, rand(100000, 999999));    
    
    //Local Transaction time
    $iso8583->addData(12, date('His'));    
    
    //Local Transaction Date
    $iso8583->addData(13, date('md'));    
    
    //Expiry Date
    $iso8583->addData(14, '1212');    
    
    //Capture Date
    $iso8583->addData(17, date('md'));    
    
    //Merchant Code
    $iso8583->addData(18, '5999');    
    
    //Country Code
    $iso8583->addData(19, '365');    
    
    //Country Code
    $iso8583->addData(22, '000');    // 000 = Unspecified, 010 = Manual, 020 = Magnetic stripe, 050 = Chip Card
    
    //Country Code
    $iso8583->addData(32, '06012345');    
    
    //Track 2 Data
    //$iso8583->addData(35, '');    
    
    //Retrieval Reference No
    $iso8583->addData(37, '000000005113');    
    
    //Authorization ID Response
   // $iso8583->addData(38, '');    
   // 
    //Response Code
    //$iso8583->addData(39, '');    
    
    //Agent ID -- Card Acceptor Terminal No Or Agent Id in case of transaction coming through Agent portal
    $iso8583->addData(41, '30010001');    
    
    //Card Acceptor Identification 
    $iso8583->addData(42, '300100011234567');    
    
    //Card Acceptor Name/Location - Information about Terminal .i.e. City name and county code
    $iso8583->addData(43, 'Mumbai 356');    
    
    //Transaction Currency Code
    $iso8583->addData(49, 'INR');
    
    
    //$iso8583->addData(1, '0400000000000000');
    

    //get iso string
    print $iso8583->getISO() . '<br />';
    print $len = strlen($iso8583->getISO());

    print '<br />';
    print $hx = strtoupper(dechex($len));
    print '<br />';
    print $mg = sprintf("%04s", $hx);
    print '<br />';
    print hexdec($hx);
    
    $iso8583->addISOLiterals('ISO');
    $iso8583->addMessage($mg);
    $iso8583->addAdditionalHeader('016000075');
    
    //print '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    print '<br /><br /><br />';
    print 'Bitmap:' .$iso8583->getBitmap() . '<br />';
    print 'Bitmap Length :' .strlen($iso8583->getBitmap());
    print '<pre>';
    print_r($iso8583->getData());
    print $iso8583->getISOwithHeaders() . '<br />';
    //exit;    
    exit('Card Load END');
    
    
    
    
    
    
    
    $iso8583->addMTI('0800');
    $iso8583->addData(7, date("mdHis"));
    //$iso8583->addData(7, '0904072533');
    $iso8583->addData(11, rand(100000, 999999));
    //$iso8583->addData(11, '101033');
    $iso8583->addData(70, '001');
    $iso8583->addData(1, '0400000000000000');
    //$iso8583->addData(1, '0400000000000000');
    $iso8583->addISOLiterals('ISO');
    $iso8583->addMessage('0067');
    $iso8583->addAdditionalHeader('006000075');

    //get iso string
    print $iso8583->getISO() . '<br />';

    //print '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    print '<br /><br /><br />';
    print 'Bitmap:' .$iso8583->getBitmap() . '<br />';
    print 'Bitmap Length :' .strlen($iso8583->getBitmap());
    print '<pre>';
    print_r($iso8583->getData());
    print $iso8583->getISOwithHeaders() . '<br />';
    //exit;    
    exit('END');
     
    $iso = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    $iso8583->addISOwithHeader($iso);
    print 'Adding Functionlity <br /><br />';
    //get parsing result
    print 'RISO: '. $iso. "<br />";
    
    print 'HISO: '. $iso8583->getISOwithHeaders(). "<br />";
    
    print 'LISO: '. $iso8583->getISO(). "<br />";
    
    print 'MTI: '. $iso8583->getMTI(). "<br />";
    
    print 'Bitmap: '. $iso8583->getBitmap(). "<br />";
    
    print 'Data Element: '; print_r($iso8583->getData());
    
    exit;
    //$iso8583->addAdditionalHeader($header);
    //$iso8583->addMessage($message);
    //$iso8583->addISOLiterals('ISO');
    
    //exit;
    /*
    
    $iso = '0800822000000000000004000000000000000904072533101033001';
    print '<br /><br /><br />';
    print $iso;
    print '<br />';
    print $iso8583->getISO() . '<br />';
    print  date("mdHis");
    echo '<pre>';
    print_r($iso8583->getData());
    exit;
    $header  = '006000075';
    $isoLiterals = 'ISO';
    $message = '0069';
    print "Length ".strlen($iso) . "<br />";
    
    //add data
    $iso8583->addISO($iso);
    $iso8583->addAdditionalHeader($header);
    $iso8583->addMessage($message);
    $iso8583->addISOLiterals('ISO');

    if($iso8583->validateISO()) {
        print "VALID <br />";
    } else  {
        print "Invalid <br />";
    }

    //get parsing result
    print 'ISO: '. $iso. "<br />";
    print 'MTI: '. $iso8583->getMTI(). "<br />";
    print 'Bitmap: '. $iso8583->getBitmap(). "<br />";
    print 'Data Element: '; print_r($iso8583->getData());
    print 'Formated ISO: '; $iso8583->getISOwithHeaders();
    print '<br /><br /><br />';
    
    
    exit;
    
    //$iso = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    $iso = '0800822000000000000004000000000000000904072533101033001';
    print "Length ".strlen($iso) . "<br />";
    
    
  


    //add data
    $iso8583->addISO($iso);

    if($iso8583->validateISO()) {
        print "VALID <br />";
    } else  {
        print "Invalid <br />";
    }

    //get parsing result
    print 'ISO: '. $iso. "<br />";
    print 'MTI: '. $iso8583->getMTI(). "<br />";
    print 'Bitmap: '. $iso8583->getBitmap(). "<br />";
    print 'Data Element: '; print_r($iso8583->getData());

    print '<br /><br /><br />';
    
 // $iso = '0069ISO006000075081082200000020000000400000000000000062307253310103300001';
    $iso = '081082200000020000000400000000000000062307253310103300001';
    print "Length ".strlen($iso) . "<br />";
    

    
        //add data
    $iso8583->addISO($iso);

    if($iso8583->validateISO()) {
        print "VALID <br />";
    } else  {
        print "Invalid <br />";
    }

    //get parsing result
    print 'ISO: '. $iso. "<br />";
    print 'MTI: '. $iso8583->getMTI(). "<br />";
    print 'Bitmap: '. $iso8583->getBitmap(). "<br />";
    print 'Data Element: '; print_r($iso8583->getData());

    
    
    
    print '<br /><br /><br />';

    
    
    
    
    $iso = '0800822000000000000004000000000000000516063439749039301';
    print "Length ".strlen($iso) . "<br />";
    //add data
    $iso8583->addISO($iso);


    //get parsing result
    print 'ISO: '. $iso. "<br />";
    print 'MTI: '. $iso8583->getMTI(). "<br />";
    print 'Bitmap: '. $iso8583->getBitmap(). "<br />";
    print 'Data Element: '; print_r($iso8583->getData());

    if($iso8583->validateISO()) {
        print "VALID <br />";
    } else  {
        print "Invalid <br />";
    }


exit;

*/
//        $a = new App_ApiServer_Exchange_Services();
//        $resp = $a->CardholderAuthentication('96B173576A', '12345678', '0987654321098765', '12345', '0515', '1234');        
//                echo "<pre>";print_r($resp);
//                exit;
        //$mvc = new App_Api_MVC();
//        return $mvc->CustomerAuthentication(array(
//                'MessageId'  => '123456',
//                'PAN'  => '1234567890123456',
//                'Amount'  => '123456',
//                'ExpiryDate'  => '0515',
//                'OTP'  => '1234',
//                ));
//          
        /*
        $validator = App_Validator::getInstanceByName(App_Validator::BETWEEN,array(App_Validator::TYPE_MIN_LENGTH =>0, App_Validator::TYPE_MAX_LENGTH=>20, App_Validator::TYPE_INCLUSIVE => true));
        if ($validator->isValid("1234567890") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Invalid DIGIT.');
        } 
        exit("END");

         */
        /*try {
        $validator = App_Validator::getInstanceByName(App_Validator::EMAILADDRESS);
        if ($validator->isValid("vikram0207@gmail.com") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Invalid email address given.');
        } 
        
        $validator = App_Validator::getInstanceByName(App_Validator::NOTEMPTY);
        if ($validator->isValid(" a") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Empty Value provided');
        } 
        
        
        $validator = App_Validator::getInstanceByName(App_Validator::ALPHA);
        if ($validator->isValid("asdfasdf") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Alpha');
        } 
        
        $validator = App_Validator::getInstanceByName(App_Validator::ALPHA, array(App_Validator::TYPE_ALLOWED_SPACE => true));
        if ($validator->isValid("asdfasdfasd fas  dfasdf") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Alpha 2');
        } 
        
        $validator = App_Validator::getInstanceByName(App_Validator::ALNUM, array(App_Validator::TYPE_ALLOWED_SPACE => true));
        if ($validator->isValid("asdfasdfasd fas  dfasdf213423") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Alphanumeric ');
        } 
        
        
        $validator = App_Validator::getInstanceByName(App_Validator::FLOAT);
        if ($validator->isValid("100.001") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' Float ');
        } 
        
        $validator = App_Validator::getInstanceByName(App_Validator::INT);
        if ($validator->isValid("100001") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' INT ');
        } 
        
        
        $validator = App_Validator::getInstanceByName(App_Validator::INT);
        if ($validator->isValid("100001") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' INT ');
        } 
        
        $validator = App_Validator::getInstanceByName(App_Validator::BETWEEN,array( App_Validator::TYPE_MIN_LENGTH => 1, App_Validator::TYPE_MAX_LENGTH =>10, App_Validator::TYPE_INCLUSIVE => false ));
        if ($validator->isValid("9") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' between ');
        } 
        
        $validator = App_Validator::getInstanceByName(App_Validator::BETWEEN,array( App_Validator::TYPE_MIN_LENGTH => 1, App_Validator::TYPE_MAX_LENGTH =>10, App_Validator::TYPE_INCLUSIVE => true ));
        if ($validator->isValid("10") !== true) {

            throw new InvalidArgumentException(__METHOD__ . ' between ');
        } 
        
        $validator2 = App_Validator::getInstanceByName(App_Validator::STRINGLENGTH,array(App_Validator::TYPE_MIN_LENGTH => 10,App_Validator::TYPE_MAX_LENGTH => 10));
        //$validator2 = new Zend_Validate_StringLength(array('min' => 100, 'max' => 200));        
        if($validator2->isValid("1234567890") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' Invalid Length.');                    
        }    
        //$a = new Zend_Validate_Date();
        //$a->getErrors()
        $validator = App_Validator::getInstanceByName(App_Validator::DATE,array(App_Validator::TYPE_FORMAT => 'dd-mm-yyyy'));
        //$validator2 = new Zend_Validate_StringLength(array('min' => 100, 'max' => 200));        
        if($validator->isValid("10-10-2012") !== true) {
                print_r($validator->getErrors());
                throw new InvalidArgumentException(__METHOD__ . ' Date');                    
        }    
        
        
        $validator = App_Validator::getInstanceByName(App_Validator::DATE,array(App_Validator::TYPE_FORMAT => 'dd-mm-yyyy'));
        //$validator2 = new Zend_Validate_StringLength(array('min' => 100, 'max' => 200));        
        if($validator->isValid("10-10-2012") !== true) {
                throw new InvalidArgumentException(__METHOD__ . ' Date');                    
        }    */
        
        /**
         * Note: Validating numbers
         * When you want to validate numbers or numeric values, be aware that this validator only validates digits. 
         * This means that any other sign like a thousand separator or a comma will not pass this validator. 
         * In this case you should use Int or Float. 
         */
     /*   $validator = App_Validator::getInstanceByName(App_Validator::DIGITS);
        //$validator2 = new Zend_Validate_StringLength(array('min' => 100, 'max' => 200));        
        if($validator->isValid("111") !== true) {
                print_r($validator->getErrors());            
                print_r($validator->getMessages());            
                throw new InvalidArgumentException(__METHOD__ . ' DIGITS');                    
        }    
        
        
        $validator = App_Validator::getInstanceByName(App_Validator::GREATERTHAN, array(App_Validator::TYPE_MIN_LENGTH => 10));
        if($validator->isValid("11") !== true) {
                print_r($validator->getErrors());            
                print_r($validator->getMessages());            
                throw new InvalidArgumentException(__METHOD__ . ' GREATERTHAN');                    
        }    
        
        $validator = App_Validator::getInstanceByName(App_Validator::LESSTHEN, array(App_Validator::TYPE_MAX_LENGTH => 10));
        if($validator->isValid("9") !== true) {
                print_r($validator->getErrors());            
                print_r($validator->getMessages());            
                throw new InvalidArgumentException(__METHOD__ . ' LESSTHEN');                    
        }    
        
        
        Print "Pass";
         } catch (Exception $e) {
             print $e->getMessage();
         }
        exit;*/
        
//        try {
//           
//    //MVC Registration
//        $array = array(
//            'MessageID'     =>  $messageId, 
//            'PAN'           =>  $pan,
//            'Amount'        =>  $amount, 
//            'ExpiryDate'    =>  $expiryDate, 
//            'OTP'           =>  $otp
//        );
//        
//            $mvc = new App_Api_MVC();
//            $mvc->CustomerAuthentication($array);
            /*
            $api = new App_ApiServer_Exchange_Services();
            $rs  = $api->login('vikram', 'vikram123');
            echo "<pre>111";print_r($rs);
                        
            //$rs2  = $api->EchoMessage($rs->SessionID);
             //echo "<pre>111";print_r($rs2);
             
           // print "SessionID: " .$rs->SessionID;
            //$rs3  = $api->SendMail($rs->SessionID,"vikram@transerv.co.in","Test Subject","Test Body","cardholder","123");
            // echo "<pre>111";print_r($rs3);
            
            $rsca = $api->CardholderAuthentication($rs->SessionID, "12345678", "1234567890123456", "200", "1504", "1234");
            echo "<pre>";print_r($rsca);
            */
//            exit;
//        } catch (Exception $e) {
//            echo "<pre>";print_r($e);
//        }
        /*try {
        
            $tr = new Zend_Mail_Transport_Sendmail('-freturn_to_me@example.com');
            Zend_Mail::setDefaultTransport($tr);

            $mail = new Zend_Mail();
            $mail->setBodyText('This is the text of the mail.');
            $mail->setFrom('somebody@example.com', 'Some Sender');
            $mail->addTo('vikram@transerv.co.in', 'Some Recipient');
            $mail->addCc('vikram0207@gmail.com', 'Some Recipient');
            $mail->setSubject('TestSubject');
            $mail->send();        
        } catch (Exception $e) {
            echo "<pre>";print_r($e);
        }*/
            /*
             $m = new App_Mail_HtmlMailer();
             $m->setSubject("Test Mail")
              ->addTo("vikram@transerv.co.in")
              ->addTo("vikram0207@gmail.com")
              ->sendHtmlTemplate("auth_code.phtml");
             */
             
     /*   //echo "<pre>==";print_r(App_DI_Container::get('ConfigObject'));exit;        
        echo "Operation Login Authrization: ";
        if(App_DI_Container::get('ConfigObject')->operation->loginauth->sendsms){
            echo "Allowed to Send SMS";
        } else {
            echo "Not Allowed to Send SMS";
        }
        
        echo "<br /><br />";
        echo "Agent Login Authrization: ";
        if(App_DI_Container::get('ConfigObject')->agent->loginauth->sendsms){
            echo "Allowed to Send SMS";
        } else {
            echo "Not Allowed to Send SMS";
        }
        */
        //Send Download Link
     /*
      try {
        $mvc = new App_Api_MVC_Transactions();
        $flg = $mvc->sendDownloadLink('9899195914');
        } catch (Exception $e) {
            print $e->getMessage();exit;
        }
        //echo "<pre>";print_r($flg);
        if($flg) {
            echo "<br />Download link send successfully<br /><br />";
        } else {
            //print '<br />';
            print 'ERROR:' .$mvc->getError() . '<br />';
            echo "<br />Download link send Failed <br />";
            //echo $ecs->getError();

        }
          */
        //MVC Registration
        $array = array(
            'CAFNumber'     => 'CAFNUMBER234567', 
            'FirstName'     => 'Vikram',
            'LastName'      =>  'Singh', 
            'MobileNumber'  =>  '9899195914', 
            'DeviceID'      =>  'SAMSUNGi9001', 
            'CRN'           =>  '3333330000001167', 
            'CustommerType' =>  'mvcc'
        );
      
        
        //Cardholder registration with MVC
        try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->Registration($array);
        } catch (Exception $e) {
            print $e->getMessage();
        }
        if($flg) {
            echo "<br />Customer Registered successfully<br /><br />";
        } else {
            echo "<br />Customer Registeration Failed <br />";
            echo $mvc->getError();

        }
             
                
                
        
        //Cardholder Registration and Cardload        
        /*
        $cardhoderArray =    array(
            'id'            =>  '123',
            'CRN'           =>  '1234567890123456',
            'First Name'    =>  'Vikram',
            'Family Name'   =>  'Singh',
            'Mothers Maiden Name'   =>  'Singh',
            'Gender'        =>  'Male',
            'Address1'      =>  'D-280 Chhattarpur',
            'Country'       =>  '123',
            'City'          =>  '21',
            'Zip Code'      =>  '110074',
            'Userdefinedfield3' =>  '3'        
            );
            try {
                $ecs = new App_Api_ECS();
                $flg = $ecs->cardholderRegistration($cardhoderArray);
                if($flg) {
                    echo "Cardholder Resgister Successfully<br /><br />";
                } else {
                    echo "Cardholder Resgister Failed <br />";
                    echo $ecs->getError();
                    
                }
                
                $cardhoderArray['ProductCode']      =  'P03';
                $cardhoderArray['currency_code']    =  '356';//356-INR, 840-USD
                $cardhoderArray['amount']            = '250';
               
                $flg = $ecs->FirstTimeCardLoad($cardhoderArray);
                if($flg) {
                    echo "Cardholder Account Load Successfully<br /><br />";
                } else {
                    echo "Cardholder Account Load Failed <br />";
                    echo $ecs->getError();
                    
                }
                
            } catch (Exception $exc) {
                echo $exc->getMessage();
            }
            exit;
         * */
        
        /*
        //Get Account Info
        $array = array(
            'CRN'     => '123456789012', 
        );
        
        //Cardholder registration with MVC
        try {
            $mvc = new App_Api_MVC();
            $flg = $mvc->getAccountInfo($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Got info Successfully<br /><br />";
                } else {
                    echo "Got info Failed <br />";
                    echo $ecs->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
        */
        /*
        $array = array(
            'MVCPAN' => '1234567890123456',
            'MVCExpiryDate' => '1234567890123456',
            'MVCCVV' => '1234567890123456',
            'MVCAmount' => '200'                
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC();
            $flg = $mvc->queryMvcStatus($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Query MVC Status info Successfully<br /><br />";
                } else {
                    echo "Query MVC Status info Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }*/
        
       /*
        $array = array(
             'CRN' => '123456',
            'FromDateTime' => '2012-11-01 00:00:00', //date('Y-m-d h:m:s'),
            'ToDateTime' => '2012-11-30 23:59:59',
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC();
            $flg = $mvc->queryMvcTransaction($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Query MVC Transaction Successfully<br /><br />";
                } else {
                    echo "Query MVC Transaction Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
        */
        
        
       /*
        $array = array(
             'CRN' => '123456',
             'MobileNumber' => '9899195914',
            'DeviceID' => '123456789',
            'RequestRefNumber' => '123456',

                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->ResendActivationCode($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Resend activation code Successfully<br /><br />";
                } else {
                    echo "Resend activation code Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
        */
        
        
       /*
        $array = array(
             'CRN' => '123456',
            'RequestRefNumber' => '123456',            
            'OldMobileNumber' => '9717204400',
            'NewMobileNumber' => '9899195914'
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->UpdateMobileNumber($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Update Mobile Number Successfully<br /><br />";
                } else {
                    echo "Update Mobile Number Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
        */
       
        /*
        $array = array(
             'CRN' => '123456',
            'RequestRefNumber' => '123456',            
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->BlockAccount($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Block Account Successfully<br /><br />";
                } else {
                    echo "Block Account Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
         */
        
      /*  
        $array = array(
             'CRN' => '123456',
            'RequestRefNumber' => '123456',            
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->UnblockAccount($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Block Account Successfully<br /><br />";
                } else {
                    echo "Block Account Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
    */
   
        
        /*
        $array = array(
             'CRN' => '123456',
            'RequestRefNumber' => '123456',            
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC_Transactions();
            $flg = $mvc->CloseAccount($array);
            $info = $mvc->getLastResponse();
            
            //var_dump($info);
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Block Account Successfully<br /><br />";
                } else {
                    echo "Block Account Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }
        */
       
        /*
        $array = array(
             'CRN' => '123456',
            'EchoData' => '1234567',            
                );
        //Query MVC Status Request
        try {
            $mvc = new App_Api_MVC();
            $flg = $mvc->TransactionHistory($array);
            $info = $mvc->getLastResponse();
            print "Account Info:" . "\n";
             echo "<pre>";print_r($info);
            
            //echo "<pre>";print_r($info);
                if($flg) {
                    echo "Block Account Successfully<br /><br />";
                } else {
                    echo "Block Account Failed <br />";
                    echo $mvc->getError();
                    
                }            
        } catch (Exception $e) {
            print $e->getMessage();
        }      
       */
         
        
    }
    
    
    public function newAction()
    {
             $m = new App_Mail_HtmlMailer();
             $m->setSubject("Test Mail")
              ->addTo("vikram@transerv.co.in")
              ->sendHtmlTemplate("auth_code.phtml");
//              ->setViewParam('authcode', $authCode)
//              ->setViewParam('email', substr($data['email'], 0, strpos($data['email'], "@")).'@')
//              ->setViewParam('name', $data['name'])
//              ->setViewParam('user_ip', MyLib_Controller_Action_Helper_Util::getclientIp())
//              ->setViewParam('host_name', MyLib_Controller_Action_Helper_Util::gethostname())       
              //->sendHtmlTemplate("authcode.phtml");
             exit;
    }
    /**
     * Static Pages
     *
     * @return void
     */
    public function staticAction()
    {
        $page = $this->getRequest()->getParam('page');
        
        if (empty($page)) {
            throw new Exception('Invalid static page identifier');
        } else {
            $this->render($page);
        }
    }
    
    
    public static function getIP()
    {
        
    }
    
    
    public function testsmsAction()
    {

        try {
        $sms = new App_SMS_SendSMS();
         $objAlert =  new Alerts();
         $chSmsMsg = 'Dear Customer, We regret to inform you that your Axis Bank Shmart!Pay Card could not be recharged at this moment.';
         $chSmsData = array('mobile1'=>'+919810780690', 'smsMessage'=>$chSmsMsg);
         $resp  = $objAlert->sendCardholderLoadFund($chSmsData,'agent');
        
        if($resp === false) {
            print 'Sending SMS fail. <br />';
            print $sms->getMessage();
        } else {
            print 'Sending SMS Successful. <br />';            
        }
        
        //echo "<pre>";print_r($resp);
        //exit;
        } catch (Exception $e) {
           print $e->getMessage();
           echo "<pre>";print_r($e);
        }
        exit('END');
    }
    
    public function testexapiAction() {
        try {
        $soapClient = new SoapClient(null, array(
            //'location' => 'http://api.shmart.local/services/exchange',
            'location' => 'http://api.shmart.local/services/exchange',
            'uri'   => 'http://api.shmart.in',
            'trace' => 1
        ));
//        $paramArray = array(
//            new SoapParam('vikram','username'),
//            new SoapParam('vikram123','password')
//        );
        //$paramArray = array('username' => 'vikram', 'password' => 'vikram123');
        
        $obj = new stdClass();
        $obj->username = 'electracard';
        $obj->password = 'ff8192593bb21da015a2';
        //$resp = $soapClient->__soapCall("Login",$obj);
        //
        //$arr[] = new SoapParam('vikram', 'username');
        //$arr[] = new SoapParam('vikram123', 'password');
        $resp = $soapClient->Login('electracard','ff8192593bb21da015a2');
        
        print '<pre>';
        //print 'Request Header: ' . htmlentities($soapClient->__getLastRequestHeaders()) . '<br /><br />';
        print 'Request : ' . htmlentities($soapClient->__getLastRequest()) . '<br /><br />';
        //print 'Response Header: ' . htmlentities($soapClient->__getLastResponseHeaders()) . '<br /><br />';
        print 'Response : ' . htmlentities($soapClient->__getLastResponse()) . '<br /><br />';
        print '<pre>';        
        
        $resp2 = $soapClient->EchoMessage($resp->SessionID);
        
        print '<pre>';
        //print 'Request Header: ' . htmlentities($soapClient->__getLastRequestHeaders()) . '<br /><br />';
        print 'Request : ' . htmlentities($soapClient->__getLastRequest()) . '<br /><br />';
        //print 'Response Header: ' . htmlentities($soapClient->__getLastResponseHeaders()) . '<br /><br />';
        print 'Response : ' . htmlentities($soapClient->__getLastResponse()) . '<br /><br />';
        print '<pre>';        
        $resp3 = $soapClient->CardholderAuthentication($resp->SessionID, "123456", '1234567890123456', '12343', '1502', '1234');
        
//        echo "<pre>";print_r($resp);
//        echo "<pre>";print_r($resp2);
//        echo "<pre>";print_r($resp3);
        
        
        print '<pre>';
       // print 'Request Header: ' . htmlentities($soapClient->__getLastRequestHeaders()) . '<br /><br />';
        print 'Request : ' . htmlentities($soapClient->__getLastRequest()) . '<br /><br />';
        //print 'Response Header: ' . htmlentities($soapClient->__getLastResponseHeaders()) . '<br /><br />';
        print 'Response : ' . htmlentities($soapClient->__getLastResponse()) . '<br /><br />';
        print '<pre>';

       $resp4 = $soapClient->Logoff($resp->SessionID);
        print '<pre>';
       // print 'Request Header: ' . htmlentities($soapClient->__getLastRequestHeaders()) . '<br /><br />';
        print 'Request : ' . htmlentities($soapClient->__getLastRequest()) . '<br /><br />';
        //print 'Response Header: ' . htmlentities($soapClient->__getLastResponseHeaders()) . '<br /><br />';
        print 'Response : ' . htmlentities($soapClient->__getLastResponse()) . '<br /><br />';
        print '<pre>';
        
//        print_r($resp);

        } catch (SoapFault $e) {
            //print 'ERROR';
//        print '<pre>';
//        print 'Request Header: ' . htmlentities($soapClient->__getLastRequestHeaders()) . '<br /><br />';
//        print 'Request : ' . htmlentities($soapClient->__getLastRequest()) . '<br /><br />';
//        print 'Response Header: ' . htmlentities($soapClient->__getLastResponseHeaders()) . '<br /><br />';
//        print 'Response : ' . htmlentities($soapClient->__getLastResponse()) . '<br /><br />';
//        print '<pre>';
            
            echo "<pre>";print_r($e);
        }
        exit;        
    }
    
       
    public function getisoinfoAction(){
            $iso8583	= new App_ISO_ISO8583();     

    
//    $iso = '0069ISO006000075081082200000020000000400000000000000062307253310103300001';
//        print substr($iso, 0, 4) . "<br />";
//        print substr($iso, 4, 3) . "<br />";
//        print substr($iso, 8, 9) . "<br />";
//        print substr($iso, 17);
//exit;    
    //$iso = '0067ISO0060000750800822000000000000004000000000000000904072533101033001';
    //$iso = '0045ISO016000075081082200000020000000400000000000000121906492';
    //$iso = '008CISO01600007502107230E0010E808000163333330000000185870000000000000200010914561900999114561901095999356064199530000000051133073072730010001356';
    
    //$iso = '008CISO01600007502107230E0010E80800016333333000000018587000000000000020001091456190099911456190109599935606419953000000005113X326730030010001356';
            //0067ISO0060000750800822000000000000004000000000000000623072533101033301
    $iso = '00AAISO0160000750200F238E00108C08000040000000000000016333333000000018587000000000000250001091825150099911825150109010959993560641995300000000511330010001300100011234567356001';
    
    //printf('%b','0045ISO016000075');exit;
    $iso8583->addISOwithHeader($iso);
    print '<pre>';
    print 'MTI :'. $iso8583->getMTI() . "\n";
    print 'Bitmap :' .$iso8583->getBitmap() . "\n";
    print_r($iso8583->getData());
    print '<br /><br /><br />';

    exit;
    
    }
    public function testrndAction() {
        $objs = new Crons();
        $res = $objs->getCronInfo(array('cron_id'=>'2'));
        
       
        
//        echo "<pre>";
//        print_r($_SERVER);
//        print Util::getIP();
//        exit;
    }
    
    
    public function rndxmlAction()
    {
        $xml = '<soapenv:Envelope
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:aaaaaaaa="http://webservice.epms.com/">
<soapenv:Header/>
<soapenv:Body>
<aaaaaaaa:callTransactionHistory>
<arg0>
<!--Mandatory:-->
<cardNumber>3333330000000987</cardNumber>
<!--Mandatory:-->
<channel>IVR</channel>
<!--Optional:-->
<componentAuthKey></componentAuthKey>
<!--Optional:-->
<componentId></componentId>
<!--Optional:-->
<expiryDate></expiryDate>
<!--Optional:-->
<ip>10.10.10.144</ip>
<!--Mandatory:-->
<passCodeFlag>N</passCodeFlag>
<!--Optional:-->
<passCodeValue></passCodeValue>
<!--Optional:-->
<password></password>
<!--Optional:-->
<requestDateTime></requestDateTime>
<!--Mandatory:-->
<serviceCode>402</serviceCode>
<!--Mandatory:-->
<sessionKey>ABCD</sessionKey>
<!--Optional:-->
<terminalType></terminalType>
<!--Optional:-->
<txnPassFlag></txnPassFlag>
<!--Optional:-->
<txnPassword></txnPassword>
<!--Mandatory:-->
<userId>ubpuser</userId>
<!--Optional:-->
<fetchFlag>1</fetchFlag>
<!--Optional:-->
<fromDate></fromDate>
<!--Optional:-->
<noOfTransactions>5</noOfTransactions> 
<!--Optional:-->
<toDate></toDate>
</arg0>
</aaaaaaaa:callTransactionHistory>
</soapenv:Body>
</soapenv:Envelope>';
        //print htmlentities($xml);
        //print '<br /><br /><br />';
        
        $a = strstr($xml, ':callTransactionHistory',true);
        $b = strrpos($a, '<');
        $ns = substr($a, $b+1);
        //exit;
        
        
        $sxml = simplexml_load_string($xml);
        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
        //$sxml = simplexml_load_string($xml);
//        //foreach($sxml->xpath('//ns1:AuthenticationRequest') as $header)
//        $a = strpos(strtolower($xml), '<soapenv:body>');
//        $b = strpos(strtolower($xml), ':calltransactionhistory');
//        print $a . ' : ' . $b . '<br />';
//        $s =  substr($xml,$a+14, $b - $a+14);
//        
//        print $s . '<br />';
//        $s = str_replace('<', '', $s);
//        $s = str_replace('>', '', $s);
//        $s = str_replace(':', '', $s);
//        print '*'.$s.'*';
//        exit;
        foreach($sxml->xpath("//".$ns.":callTransactionHistory") as $header)
        {
            //$arr = $header->Message->attributes();
            
            $ar = array(
                   'cardNumber'  => (string) $header->arg0->cardNumber,
                   'sessionKey'  => (string) $header->arg0->sessionKey,                    
                   'fetchFlag'   => (string) $header->arg0->fetchFlag,                    
                   'fromDate'    => (string) $header->arg0->fromDate,                    
                   'noOfTransactions'  => (string) $header->arg0->noOfTransactions,                    
                   'toDate'      => (string) $header->arg0->toDate,                    
           );
            //echo "<pre>";
            //print_r($arr);
            //var_dump($header);
            //print_r($ar);
        }
        echo "<pre>";print_r($ar);
        exit;
    }

    public function noscriptAction(){
        // use the login layout
        $this->_helper->layout()->setLayout('withoutlogin');
        
    }
    
    public function nocookieAction(){
        // use the login layout
        $this->_helper->layout()->setLayout('withoutlogin');
        
    }
    
    
    public function readfileiso() {
        $buffer = file_get_contents("C:\\xampp\\htdocs\\api\\iso120.log");
        $length = filesize("C:\\xampp\\htdocs\\api\\iso120.log");

        if (!$buffer || !$length) {
          die("Reading error\n");
        }

        $_buffer = '';

        $length = bin2hex(mb_substr($buffer, 0, 2));
        print $length . PHP_EOL;
        $p2 = mb_substr($buffer, 2,16);
        print $p2 . PHP_EOL;
        $p3 = bin2hex(mb_substr($buffer, 18,16));
        print $p3 . PHP_EOL;
        $p4 = mb_substr($buffer, 34);
        $iso = $length . $p2 . $p3 . $p4;
        return $iso;
        //print $iso;
        //exit;
    }
    
 public function convert2ascii($buffer)   
 {
     
    //$length = strtoupper(bin2hex(mb_substr($buffer, 1, 2)));
    $length = (mb_substr($buffer, 0, 4));
    print 'Length :'.$length.'<br />';
    $p2 = mb_substr($buffer, 4,16);
    print 'P2 :'.$p2. '<br />';
    $p3 = (mb_substr($buffer, 20));
    //print 'P3 :'.$p3.'<br />';
    //$p4 = mb_substr($buffer, 36);
    ///print 'P4 :'.$p4.'<br />';
    $iso = $length . $p2 . $p3;// . $p4;
    print $iso.'**<br />';
    return $iso;
    //print $iso;
      
     //print $buffer.'**<br />';
    $length = strtoupper((mb_substr($buffer, 0, 4)));
    print 'Length :'. $length.'<br />';
    $p2 = mb_substr($buffer, 4,16);
    print 'P2 :' .$p2.'**<br />';
    $p3 = (mb_substr($buffer, 20,16));
    print 'P3 :'.$p3.'**<br />';
    //print $p3.'**<br />';
    //print bin2hex(mb_substr($buffer, 34,50)) . '** <br />';
    $p4 = mb_substr($buffer, 34);
    //$p4 = mb_substr($buffer, 34,50);
    
    print $p4.'**<br />';    
    //$p5 = mb_substr($buffer, 50);
    //print $p5.'**<br />';    
    $iso = $length . $p2 . $p3 . $p4;// . $p5;
    print 'ISO :'.$iso.'<br />';
    return $iso;
    //print $iso;
 }
}
    


