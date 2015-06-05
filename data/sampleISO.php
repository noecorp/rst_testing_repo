<?php

//ISO Creation
$iso8583	= new App_ISO8583();
//add data
$iso8583->addMTI('0800');
$iso8583->addData(7, date("mdHis"));
$iso8583->addData(11, rand(1000, 999999));
$iso8583->addData(70, '301');

//get iso string
print $iso8583->getISO();
exit;


//ISO Parsing
$iso8583	= new App_ISO8583();

//$iso	= '0800822000000000000004000000000000000516063439749039301';
$iso	= '0060000750800822000000000000004000000000000000904072533101033001';



//add data
$iso8583->addISO($iso);


//get parsing result
print 'ISO: '. $iso. "\n";
print 'MTI: '. $iso8583->getMTI(). "\n";
print 'Bitmap: '. $iso8583->getBitmap(). "\n";
print 'Data Element: '; print_r($iso8583->getData());



//Enhancement

//Adding additional header as per ECS requirement

    $iso8583->addMTI('0800');
    $iso8583->addData(7, date("mdHis"));
    $iso8583->addData(11, rand(1000, 999999));
    $iso8583->addData(70, '301');
    $iso8583->addISOLiterals('ISO');
    $iso8583->addMessage('0069');
    $iso8583->addAdditionalHeader('006000075');

    //get iso string
    print $iso8583->getISO() . '<br />';
    print $iso8583->getISOwithHeaders();
    exit;    