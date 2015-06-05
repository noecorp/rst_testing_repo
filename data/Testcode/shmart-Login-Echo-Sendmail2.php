<?php
  //$client = new SoapClient("http://api.shmart.local/services/exchange?wsdl",array(
  $client = new SoapClient(null,array(
    "trace"      => 1,
    "exceptions" => 0,
    "uri"        =>  'http://www.axiswebservice.net1.com/',
    "type"        =>  'sas',
    "result"        =>  'sas',
    "location"   =>   'http://qa-api.shmart.in/services/exchange',
      //'version' =>SOAP_1_2
      )
  );
  
  $xmlDocument = 	'<Message id="53947762">
<SessionID>123456</SessionID>
<PAN>1234567890123456</PAN>
<Amount>10000</Amount>
<ExpiryDate>1502</ExpiryDate>
<OTP>1234</OTP>
</Message>';
 
 
$param = new SoapVar(
            $xmlDocument,
            XSD_ANYXML
);
$response = $client->AuthenticationRequest($param);  
  
  //$resp = $client->__soapCall("AuthenticationRequest",$param);

  print "<pre>";
  print "Request : " . htmlentities($client->__getLastRequest()) . "\n" ;
  print "Response : " . htmlentities($client->__getLastResponse()) . "\n" ;
  print "</pre>";
  
  
  exit;
  
//print $client->dummyfunc("abadfasd");
$resp = $client->__soapCall(
        "Login",
        array(
			new SoapParam('vikram','username'),
			new SoapParam('vikram123','password'),
		)
		);

  print "<pre>";
 print "Request : " . htmlentities($client->__getLastRequest()) . "\n" ;
  print "Response : " . htmlentities($client->__getLastResponse()) . "\n" ;
  print "</pre>";

  print "<br /><br /><br />";
  print "SessionId :" .$resp->SessionID . "\n";
  
   $respEcho =  $client->__soapCall(
        "EchoMessage",
        array(new SoapParam($resp->SessionID,'SessionID')));

 print "<pre>";
 print "Request : " . htmlentities($client->__getLastRequest()) . "\n" ;
 print "Response : " . htmlentities($client->__getLastResponse()) . "\n" ;
 print "</pre>";

 print "<br /><br /><br />";
 

   $respCA =  $client->__soapCall(
        "EchoMessage",
        array(
			new SoapParam($resp->SessionID,'SessionID'),
			new SoapParam('123456','MessageID'),
			new SoapParam('1234567890123456','PAN'),
			new SoapParam('12345678','Amount'),
			new SoapParam('0515','ExpiryDate'),
			new SoapParam('1234','OTP'),
			
		));
		
   //$client->CardholderAuthentication($resp->SessionID, "12345678", "1234567890123456", "200", "0215", "1234");

 print "<pre>";
 print "Request : " . htmlentities($client->__getLastRequest()) . "\n" ;
 print "Response : " . htmlentities($client->__getLastResponse()) . "\n" ;
 print "</pre>";

 print "<br /><br /><br />";
 
 
