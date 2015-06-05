<?php
//Customer Authentication
$post_string = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://api.shmart.in/">

    <soapenv:Body>
    <sas:Login>
        <username>vikram</username>
        <password>vikram123</password>
</sas:Login>
</soapenv:Body>
</soapenv:Envelope>';


$post_string_test = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://api.shmart.local/">
<soapenv:Header/>
    <soapenv:Body>
    <sas:test>
        <abc>1234566</abc>
</sas:test>
</soapenv:Body>
</soapenv:Envelope>';


$post_string_customerauth = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
				xmlns:sas="http://api.shmart.local/services/exchange">
			<soapenv:Header/>
				<soapenv:Body>
					<sas:CardholderAuthentication>
							<MessageID>1234555</MessageID>
								<SessionID>8DA062C3A9</SessionID>
								<PAN>1234567890123456</PAN>
								<Amount>12343</Amount>
								<ExpiryDate>1502</ExpiryDate>
								<OTP>1234</OTP>
					</sas:CardholderAuthentication>
				</soapenv:Body>
		</soapenv:Envelope>';


$post_string_customerauth2 = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://api.shmart.local/">
<soapenv:Header/>
<soapenv:Body>
		<sas:AuthenticationRequest>
			<Message id="53947762" id2="123132123123">
				<SessionID>123456</SessionID>
				<PAN>1234567890123456</PAN>
				<Amount>10000</Amount>
				<ExpiryDate>1502</ExpiryDate>
				<OTP>1234</OTP>
			</Message>
		</sas:AuthenticationRequest>
</soapenv:Body>
</soapenv:Envelope>';

$post_string_custauth3 = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://www.axiswebservice.net1.com/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:AuthenticationRequest><Message id="53947762">
<SessionID>123456</SessionID>
<PAN>1234567890123456</PAN>
<Amount>10000</Amount>
<ExpiryDate>1502</ExpiryDate>
<OTP>1234</OTP>
</Message></ns1:AuthenticationRequest></SOAP-ENV:Body></SOAP-ENV:Envelope>';
	

$post_string_custAuth4 = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas123="http://qa-api.shmart.in/services/exchange">
<SOAP-ENV:Header/>
<SOAP-ENV:Body>
<sas:AuthenticationRequest>
<Message  id="123456">
<SessionID>820121219171111</SessionID>
<PAN>4199530000000001</PAN>
<Amount>10000</Amount>
<ExpiryDate>0701</ExpiryDate>
<OTP>1234</OTP>
</Message>
</sas:AuthenticationRequest>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
	
$post_string_balance = '<soapenv:Envelope
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:web="http://qa-api.shmart.in/services/exchange">
<soapenv:Header/>
<soapenv:Body>
<web:callBalanceInquiry>
<arg0>
<!--Mandatory:-->
<cardNumber>3333330000000201</cardNumber>
<!--Mandatory:-->
<channel>IVR</channel>
<!--Optional:-->
<componentAuthKey></componentAuthKey>
<!--Optional:-->
<componentId></componentId>
<!--Optional:-->
<expiryDate>1705</expiryDate>
<!--Mandatory:-->
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
<serviceCode>401</serviceCode>
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
</arg0>
</web:callBalanceInquiry>
</soapenv:Body>
</soapenv:Envelope>';


	
$post_string_transactionhistory = '<soapenv:Envelope
xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:web="http://qa-api.shmart.in/services/exchange">
<soapenv:Header/>
<soapenv:Body>
<web:callTransactionHistory>
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
</web:callTransactionHistory>
</soapenv:Body>
</soapenv:Envelope>';

	
$post_string_balance2 = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.epms.com/">
<SOAP-ENV:Header/>
<SOAP-ENV:Body>
<web:callBalanceInquiry>
<arg0>
<!--Mandatory:-->
<cardNumber>3333330000000201</cardNumber>
<!--Mandatory:-->
<channel>IVR</channel>
<!--Optional:-->
<componentAuthKey></componentAuthKey>
<!--Optional:-->
<componentId></componentId>
<!--Optional:-->
<expiryDate>1705</expiryDate>
<!--Mandatory:-->
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
<serviceCode>401</serviceCode>
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
</arg0>
</web:callBalanceInquiry>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

$post_string_transactionhistory2 = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservice.epms.com/">
<SOAP-ENV:Header/>
<SOAP-ENV:Body>
<web:callTransactionHistory>
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
</web:callTransactionHistory>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';

 
//$var = $post_string_transactionhistory;
//$var = $post_string_transactionhistory2;
//$var = $post_string_balance;
//$var = $post_string_custAuth4;
$var = $post_string_balance2;

$soap_do = curl_init(); 
//curl_setopt($soap_do, CURLOPT_URL,            'http://196.37.195.93:7001' );   
curl_setopt($soap_do, CURLOPT_URL,            'http://qa-api.shmart.in/services/exchange' );   
//curl_setopt($soap_do, CURLOPT_URL,            'http://api-dev.shmart.in/services/exchange' );   
//curl_setopt($soap_do, CURLOPT_URL,            'http://api.shmart.local/services/exchange' );   
curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10); 
curl_setopt($soap_do, CURLOPT_TIMEOUT,        10); 
curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);  
curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
curl_setopt($soap_do, CURLOPT_POST,           true ); 
curl_setopt($soap_do, CURLOPT_POSTFIELDS,    $var); 
curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array('Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($var) )); 
//curl_setopt($soap_do, CURLOPT_USERPWD, $user . ":" . $password);

$result = curl_exec($soap_do);
$err = curl_error($soap_do); 


// Then, after your curl_exec call:
//$header_size = curl_getinfo($soap_do, CURLINFO_HEADER_SIZE);
//$header = substr($result, 0, $header_size);
//$body = substr($response, $header_size);
$header = curl_getinfo($soap_do);
//print $header_size;
print '<br /><br />';
print_r($header);
print '<br /><br />';
print $result;exit;
//echo "<pre>";print_r($result);
//echo "<pre>";print_r($err);

print 'Request : ' . htmlentities($var);
print '<br /><br />';
print 'Response : ' . htmlentities($result);
print '<br /><br />';
print 'Error : ' . htmlentities($err);

print "<pre>";var_dump($soap_do);

?>