<?php

if(!defined('CRON_PATH')){
    define('CRON_PATH', realpath(dirname(__FILE__) . '/../../'));
}
//echo CRON_PATH . '/cli.php'; exit;
require_once CRON_PATH . '/cli.php';





 $m = new App\Messaging\System\Operation();

$newArr = array(
                        'Typecode' =>'RMRG',
                        'Flat Rate' =>'10',
                        'Percent' =>'20',
                       
                    );
  // SEND NOTIFICATION TO ADMIN USERS
                  
                  //$feeDetails = $feeplanModel->finddetailsById($fid);
                  $mailData['email'] = ADMIN_EMAIL_IDS;
                  $mailData['product_name'] = 'NA';
                  $mailData['limit_category'] = 'FEE PLAN: '.$feeDetails['name'];
                  $mailData['param_name'] = 'FEE ITEMS';
                  $mailData['old_value'] = NEW_ADDITION;
                  $mailData['new_value'] = $newArr;
                  $m->limitUpdates($mailData);
                  
                  
exit;                  
                /*    $ref = new Reference();
                    $ref->customSMSLogger(array(
                        'type' => 'p_sms',
                        'product_id' => '3',
                        'txn_no' => '9999',
                        'method' => 'CardTransaction',
                        'mobile' => '9899195914',
                        'message' => 'HELLO HELLO',
                        'exception' => ''            
                    ));

                    exit;*/

class SendMailTemplate extends Zend_Mail{
   //private $_subject;
   
   
    public function __construct($charset = null) {
        parent::__construct($charset);
        $this->setFrom(App_DI_Container::get('ConfigObject')->mail->sender->fromemail);
    }
    
    public function sendMail($to,$subject,$mailtext)
    {
            $this->setSubject($subject);
            $config = array('ssl' => 'tls', 'port' => App_DI_Container::get('ConfigObject')->mail->mandrill->port, 'auth' => 'login', 'username' => App_DI_Container::get('ConfigObject')->mail->mandrill->username, 'password' => App_DI_Container::get('ConfigObject')->mail->mandrill->password);                
            $tr = new Zend_Mail_Transport_Smtp(App_DI_Container::get('ConfigObject')->mail->mandrill->smtp,$config);
            self::setDefaultTransport($tr);            
     
        if($to == '') {
            throw new Exception('No send to email defined');
        }
        try {
            $this->addTo($to);

            App_Logger::emaillog(array(
                'to'            => $this->getRecipients(),
                'from'          => $this->getFrom(),
                'subject'       => $this->getSubject(),
                'body'          => $mailtext,  
            ));
            $this->setBodyHtml($mailtext,$this->getCharset(), Zend_Mime::ENCODING_QUOTEDPRINTABLE);
            $this->send();
            $this->clearSubject();
            print 'Mail send successfully';
        } catch (Exception $e) {
            echo '<pre>';print_r($e);exit;
            App_Logger::log($e, Zend_Log::ERR);              
            //$this->setError($e->getMessage());
            return false;
        }
        return true;
    }
 
}






$mailArray = array (
     array('ningaraj1234@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Ningappa Chatrad, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: ningaraj1234@gmail.com </p>

<p>Password : ODQwMDQy</p>

<p>Partner Code: 65850861039</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=pBmBG6GqjifJqasFzALAbUYFT8S58DTAFvxetzpTXoilQZYFksOFqkarMZvjTuB4SyXK3Ghhza6doDPcA%2FfODMxn5s9FXIys7ASnfJVmw0oywD%2B7nWphvhRnQ%2BI2O%2FJLj%2BT%2FvRzOBBmmcD7WCbhQ%2FuCVr6jQQN%2BTjBOQYv2gDbk%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('vandna.singh80@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Vandana Singh, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: vandna.singh80@gmail.com </p>

<p>Password : NTIyNzc1</p>

<p>Partner Code: 23788091042</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=SdRej1zYvrMpzNDmKBISRk1HroxlckNYNTkuQ%2B3kIbjf%2BvoLFrtXp%2B8A2S1ELp7p%2Bq3b8wTGo4XHZox3%2B3HqDJmvoHgBrh4ydwxrkgPyOmU1hyXclyd6pkTJRU%2FSIu%2BbTAywfo2B%2Bk%2B%2B96B80klxelEKAbDEfa7j5eYECieCgqM%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('ece91@rediffmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Pinkesh  Sahu, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: ece91@rediffmail.com </p>

<p>Password : NjI2ODc0</p>

<p>Partner Code: 68597001052</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=ncAOGCZuL8JbTuStPwV6k51LB9nmyJuyBgxoPpBz5YVU%2B1Nb7k1cgRRpsOQBShsjaKhkmfK%2BWyUFt1WYY%2B%2B6cSCH7LobgwWTMWmT41F5aZiMgRrozk4tj8gI%2FJLlNrmv7e6HnOGWwhRlzBo8aA%2FL3VHimWCF5OslsZSfKcEeENw%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('rakesh.p@email.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Kiran  Kapdi, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: rakesh.p@email.com </p>

<p>Password : NjgxMjg2</p>

<p>Partner Code: 33277231054</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=TMedzQ7vifJ0dQzu%2Fud3oulvdK7DlVnF9glC61YK6H58w9kuvXUvWWMHGJf4SkIj5Fgt0n%2FKZ3hAR0a1c07nggKNT8%2FsBXE3qSH%2Fbmy6CZY8ZJDWedu0Xz0D%2Fcz%2BnxvVtWLtGRf93sUtqJRBtuc5Vti32idZdka%2FBECgcpwHh0s%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('ashish@cyberdairy.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Ashish Vishwakarma, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: ashish@cyberdairy.com </p>

<p>Password : ODc0NTY5</p>

<p>Partner Code: 28127561059</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=e6E%2Fw2dfpHShsh5iY4lVc50UK2QWWy1LrXmhASuFHLd6Y%2FlBa2O4%2BiWmjhyWf8GeDh%2FQwXQi97hkxXhnWB15E5hKksoj4fbO7NkAfAahsq9Ev5Hfbkgt7hZ4qfAzdhlJo%2F6h3q19As5y6nuyQbsuiRlQHhZvxqtqKhf8FS1H%2BZ8%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('nritm.office@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Vijay  Tiwari, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: nritm.office@gmail.com </p>

<p>Password : MTY3MjE5</p>

<p>Partner Code: 39651601061</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=Wp3ri8vAe6tq9ulryiBouxDLRMPCZBD54Zs0FAQ4381Inb%2FyshVPRFt%2FhwFsM%2FRaPCfp1SF0HknpH%2FRiPXs%2FOnz99%2FqxfidtbkKmQdd26cRCfaqN9r1PPG%2BG9xVdWPzd%2BoG%2BJop4MfCdp5nk6J3xxB9exQeLJ9eYuMOR5PlPkIM%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('aditya_itservices@rediffmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Manoj Lulu, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: aditya_itservices@rediffmail.com </p>

<p>Password : MTg0ODg4</p>

<p>Partner Code: 13962791063</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=kgcT6SXjO02V7EwT9%2Bh%2BkFGqpGVBsGNDh4OvGrPm8ajH%2FAliYohQVmFx22dRQq5ERZU%2FL%2B7HzwkgwHzsnBuOlY877OH0X50d1TO3khLnl3i%2BPO8w0rDKDudXKRiyzRxTAMR2eTuLcUNv2oxqQMqWzuOTL9WOcT5nV1nCgp6RmdM%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('siddhibjp@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Siddhi Agrawal, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: siddhibjp@gmail.com </p>

<p>Password : MjMxNDg=</p>

<p>Partner Code: 17221441064</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=dYbzLpbtmo%2FasGn2dBxhh85NjuXO%2FZQkWC%2Fip8qeIEYsGERC1TBV%2Fd0nDe4Jx4cE1ZFuzdtWh5%2FDH7uKSHdgfx%2BuDZNDSUkU5jM6TYggWYpdW5HaGQIneoALK9TRitoKDEmVy%2FyjBp%2BXSYRvEl4xCzcDo8nCPkM048iigZWdGCg%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('amitv1516@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Amit Verma, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: amitv1516@gmail.com </p>

<p>Password : MjIzMjIw</p>

<p>Partner Code: 11542801067</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=K4EPFtTVRnTpuDM0%2Bwj30MsgwPa2nvPOzTGMnUX2mRRL3cKKJXjAH5aaH%2FwbJfNhTOUNWJgpNtdEZj9nOp5QKfxcPge5hIuyPJpFz4U3NH6QKLmWTGmxCcXkBvL2%2FmNLvffovqCbfF52DEcnR99Oz8bNV2XfU0WoNZFYjTbL3JQ%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('reliable.dewas@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Azad  Sheikh, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: reliable.dewas@gmail.com </p>

<p>Password : NjY0NDQx</p>

<p>Partner Code: 25846491068</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=%2BN1Vh2SrXOGbRg3E%2FpfZRWOlSVusd46MtZbUF6IFSHDQloeBCEcCeiwEk3To7qB0qDuIhwV3D4go0PXhTgKJ%2FGMryRLlxLXa9uCkKvNSrtc9lF7JBKUT35o1fPK3qv%2F02pxDvUB7OGD7Gk5dYr%2BYWXxeTilfqpNRYtXJw3CrpVA%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('jas.charanjit@ymail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Charanjit Kaur, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: jas.charanjit@ymail.com </p>

<p>Password : NDExMzE0</p>

<p>Partner Code: 19935341081</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=754W%2BtusrBhmH48KmCXAIfYTIWfvcr7leX29JCaCX%2FRynWXxtb6mZlf0WtV9RWVHC3SEEqKvNTBk0rGuCGuQSBAIOabbC2X99vA1P0ToN8MPjPOpRhuC9h%2FaEJiH9v6Y0OpoJuM63ThLGxJupobD0yyNE%2BAL8G2quYGW6cb7gLM%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>'),
 array('Computer.ujala@gmail.com','Email verification for your Shmart! Business Partner Account','<STYLE type="text/css">

p {
font-family: verdana;
font-size: 13px;
}
</STYLE>

<p>Dear  Bipendra Prasad, </p>

<p>Your Shmart! Business Partner Account has been created.</p>

<p>User name: Computer.ujala@gmail.com </p>

<p>Password : NjcyMTY4</p>

<p>Partner Code: 28512291106</p>

<p>Click on the link to activate your account: <a href="https://partner.shmart.in/?a=GA9m23a59PkWFCb2UwkpJ26eTGpb2kunOcFq9c2BizypB%2B9Al3QArFNfyXEtUhWJ%2FsYJcRxEdevraRE0MVmRjaYQJZzcbVjL%2FNxp%2B%2FdoseANh42BorwszLtw0Z1jRtC0Zsc9Beo5MgZOc75TPcgHpO6uN9CXsl2zZ2l6oNRRL6I%3D">Click to Verify Email</a></p>

<p>Please contact us IMMEDIATELY in case you have not applied to become a Shmart! Business Partner.</p>

<p>Thank You.</p>

<p>Shmart! Support Team</p>

<p>Email ID: partner@shmart.in</p>

<br><br>

<p><u>IMPORTANT</u></p>

<p>Please do not reply to this e-mail. For any queries or suggestions, please contact your Relationship Manager. You can also email us on partner@shmart.in.</p>

<p><u>DISCLAIMER</u></p>

<p>This communication (including the attachment(s) if any) is privileged and confidential and is directed to and intended for use by the intended addressee only. Access and use of this e-mail in any manner by anyone other than the intended addressee is unauthorized.
            If you are not the intended addressee, you must not use this message, notify the sender immediately and delete the message from your system (or any copies thereof).The recipient acknowledges that TranServ may be unable to exercise control or ensure or guarantee the integrity of the text of the email message or the attachment and the text and the attachment is not warranted as to completeness and accuracy. Before opening and accessing the attachment, if any, please check and scan for virus.</p>

<p><u>TERMS AND CONDITIONS</u></p>

<p>Internet transmission lines are not encrypted and that email is not a secure means of transmission. The account holder acknowledges and accepts that such un-secure transmission methods involve security risks including possible third party interception risk of possible unauthorized alteration of data and/or unauthorized usage thereof for whatever purposes. The account holder specifically agrees to exempt the bank from, any and all responsibility/liability arising from such misuse and agrees not to hold the bank responsible for any such misuse and further agree to hold the bank free and harmless from all losses, costs, damages, expenses that may be suffered by the account holder due to any errors and delays.</p>')

);





















$mail = new SendMailTemplate();

//$mail = new SendMailTemplate();

foreach ($mailArray as $mArr) {
    echo $mArr[0] . ' : '.$mArr[1].PHP_EOL;
    $mail->sendMail($mArr[0], $mArr[1], $mArr[2]);
    $mail->clearSubject();
    $mail->clearRecipients();
echo PHP_EOL;
};
