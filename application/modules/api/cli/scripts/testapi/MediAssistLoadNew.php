<?php
//Customer Authentication
$login = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://qa-api.shmart.in/">
    <soapenv:Body>
    <sas:Login>
        <username>mediassist</username>
        <password>grs192433bb21da015a2</password>
</sas:Login>
</soapenv:Body>
</soapenv:Envelope>';


$load = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://qa-api.shmart.in">
    <SOAP-ENV:Body><ns1:MACardloadRequest><SessionID>29A0175CE4</SessionID><MAID>6810673897</MAID><HospitalMCC>12343</HospitalMCC><Amount>100</Amount><HospitalID>12343</HospitalID><HospitalTID>12145678</HospitalTID></ns1:MACardloadRequest></SOAP-ENV:Body></SOAP-ENV:Envelope>';

$loadNewFormat = '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://api.vikram/">
<SOAP-ENV:Body>
	<sas:CardTransactionRequest>
		<SessionID>783D56FDCD</SessionID>
		<TxnIdentifierType>CRN</TxnIdentifierType>
		<MemberIDCardNo>4780745100002367</MemberIDCardNo>
		<Amount>40000</Amount>
		<Currency>356</Currency>
		<Narration>Cashless Hospitalisation. Claim No. 123456</Narration>
		<WalletCode>RCI310</WalletCode>
		<TxnNo>123456</TxnNo>
		<CardType>N</CardType>
		<CorporateID></CorporateID>
		<TxnIndicator>CR</TxnIndicator>
	</sas:CardTransactionRequest>
</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
$loadNewFormat2 = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://qa-api.shmart.in/">
    <soapenv:Body>
    <sas:CardTransactionRequest>
        <SessionID>107AA4B4EF</SessionID>
				<TxnIdentifierType>CRN</TxnIdentifierType>
				<MemberIDCardNo>4780745100005063</MemberIDCardNo>
				<Amount>110000</Amount>
				<Currency>356</Currency>
				<WalletCode>RCI310</WalletCode>
				<TxnNo>98883</TxnNo>
				<CardType>N</CardType>
				<TxnIndicator>CR</TxnIndicator>
</sas:CardTransactionRequest>
</soapenv:Body>
</soapenv:Envelope>';

$loadNewFormat3='<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://api.shmart.in/">
            <SOAP-ENV:Body>
              <sas:CardTransactionRequest>
                <SessionID>460BE3B81B</SessionID>
                <TxnIdentifierType>CRN</TxnIdentifierType>
                <MemberIDCardNo>4780745100005063</MemberIDCardNo>
                <Amount>80030</Amount>
                <Currency>356</Currency>
                <WalletCode>RCI310</WalletCode>
                <TxnNo>abcd1234</TxnNo>
                <CardType>N</CardType>
                <TxnIndicator>CR</TxnIndicator>
                <Ip>127.0.0.1</Ip>
              </sas:CardTransactionRequest>
            </SOAP-ENV:Body>
          </SOAP-ENV:Envelope>';

$loadNewFormat4 ='<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://api.shmart.in/">
            <SOAP-ENV:Body>
<sas:CardTransactionRequest>
<SessionID>58BE59E0B4</SessionID>
<TxnIdentifierType>MID</TxnIdentifierType>
<MemberIDCardNo>0000002101</MemberIDCardNo>
<Amount>100012</Amount>
<Currency>356</Currency>
<WalletCode>RCI310</WalletCode>
<TxnNo>98745</TxnNo>
<CardType>N</CardType>
<TxnIndicator>DR</TxnIndicator>
</sas:CardTransactionRequest>
</SOAP-ENV:Body>
          </SOAP-ENV:Envelope>';

$loadNewFormat5='<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sas="http://api.shmart.in/">
            <SOAP-ENV:Body>
              <sas:CardTransactionRequest>
                <SessionID>AA9A5DDC6E</SessionID>
                <TxnIdentifierType>MID</TxnIdentifierType>
                <MemberIDCardNo>0000002101</MemberIDCardNo>
                <Amount>100012</Amount>
                <Currency>356</Currency>
                <WalletCode>RCI310</WalletCode>
                <TxnNo>98745</TxnNo>
                <CardType>N</CardType>
                <TxnIndicator>DR</TxnIndicator>
                <Ip>127.0.0.1</Ip>
              </sas:CardTransactionRequest>
            </SOAP-ENV:Body>
          </SOAP-ENV:Envelope>';

$loadNewFormat6 = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
xmlns:sas="http://qa-api.shmart.in/">
    <soapenv:Body>
    <sas:CardTransactionRequest>
      <SessionID>A03AC0D883</SessionID>
      <TxnIdentifierType>CRN</TxnIdentifierType>
      <MemberIDCardNo>4780742222000072</MemberIDCardNo>
      <Amount>1000</Amount>
      <Currency>356</Currency>
      <WalletCode>RCI310</WalletCode>
      <TxnNo>548964</TxnNo>
      <CardType>N</CardType>
      <TxnIndicator>DR</TxnIndicator>
</sas:CardTransactionRequest>
</soapenv:Body>
</soapenv:Envelope>';


$var = $loadNewFormat6;
//$var = $login;
//$var = $loadNewFormat;
//$var = $login;
$soap_do = curl_init(); 
//curl_setopt($soap_do, CURLOPT_URL,            'http://196.37.195.93:7001' );   
curl_setopt($soap_do, CURLOPT_URL,            'http://qa-api.shmart.in/services/ratkr' );   
//curl_setopt($soap_do, CURLOPT_URL,            'http://api.vikram/services/ratkr' );   
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
