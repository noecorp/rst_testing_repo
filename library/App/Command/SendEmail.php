<?php
/**
 * Command
 *
 * @package default
 */
class App_Command_SendEmail extends App_Command_Abstract{
    /**
     * Store the command name
     *
     * @var string
     */
    private $_commandName = 'SMTP';
    
    /**
     * Convenience method to run the command
     *
     * @param string $name
     * @param mixed $args
     * @return boolean
     */
    public function onCommand($name, $args){
        
        //Do the command
        //$mailObject = sprintf('App_Mail_%s', $args['type']);
        //$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $config = App_DI_Container::get('ConfigObject');          
        if($config->system->url->encryption == TRUE) {
             if(isset($_GET['a'])) {
                $url = $_SERVER['HTTP_HOST'] . Util::decryptURL($_GET['a']);
             } else {
                $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
             }
        } else {
                $url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];            
        }
        
        $mail = new App_Mail_HtmlMailer();
        $mail->setSubject("Sytem Notification")
                ->addTo($args['recipients'])
                ->setViewParam('user_ip',  Util::getIP())
                ->setViewParam('URL',$url)
                ->setViewParam('type',$args['type'])
                ->setViewParam('level',$args['level'])
                ->setViewParam('message',$args['message'])
                ->sendHtmlTemplate("notification_en.phtml");   
        return TRUE;
    }
}