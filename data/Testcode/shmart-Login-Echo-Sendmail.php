<?php
  //$client = new SoapClient("http://api.shmart.local/services/exchange?wsdl",array(
  $client = new SoapClient(null,array(
    "trace"      => 1,
    "exceptions" => 0,
    //"uri"        =>  'http://www.axiswebservice.net1.com/',
    //"uri"        =>  'http://api-dev.shmart.in/services/exchange',
    "uri"        =>  'http://api.shmart.local/services/exchange',
    "type"        =>  'sas',
    "result"        =>  'sas',
    //"location"   =>   'http://api-dev.shmart.in/services/exchange',
    "location"   =>   'http://api.shmart.local/services/exchange',
      //'version' =>SOAP_1_2
      )
  );
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
 
 

//$respSendMail = $client->__soapCall("sendMail",
//        array(new SoapParam($resp->SessionID,"SessionID"),
//        new SoapParam("vikram0207@gmail.com","To"),
//        new SoapParam("Test Subject","Subject"),
//        new SoapParam("Test Message","Body"),
//        new SoapParam("cardholder","UserType"),
//        new SoapParam("12","UserID"))
//        );
//CardholderAuthentication($sessionId, $messageId, $pan, $amount, $expiryDate, $otp)

/*
$respCustAuth = $client->__soapCall("sendMail",
        array(new SoapParam($resp->SessionID,"SessionID"),
        new SoapParam("123456789","MessageID"),
        new SoapParam("1234567890123456","PAN"),
        new SoapParam("200","Amount"),
        new SoapParam("1504","ExpiryDate"),
        new SoapParam("1234","OTP"))
        );


//$respSendMail = $client->sendMail($resp->SessionID, "vikram@transerv.co.in", "Test Message", "test body", "cardholder", "12");

  print "<pre>";
  print "Request : " . htmlentities($client->__getLastRequest()) . "\n" ;
  print "Response : " . htmlentities($client->__getLastResponse()) . "\n" ;
  print "</pre>";
 
 //var_dump($resp);
 //var_dump($respEcho);
 var_dump($respSendMail);
 //print $client->dummyfunc("12");
// print $client->__soapCall("dummyfunc", 
//         array(new SoapParam("aaaa","abc"))
//         );
  
//  $response = $client->__soapCall('test', 
//             array(new SoapParam('aaaaaa', 'abc')),
//             array('soapaction' => 'http://api.shmart.local/services/exchange')
//             //array('soapaction' => 'http://api.shmart.local/index/mvc?wsdl')
//          );
  
  
//  print "<pre>";
//  print "Request : " . htmlentities($client->__getLastRequest()) . "\n" ;
//  print "Response : " . htmlentities($client->__getLastResponse()) . "\n" ;
//  print "</pre>";
  //print_r($response);
  */
?>