<?php
/**
 * Allow the admins to manage Agent fee.
 *
 * @category Agent fee
 * @package operation_module
 * @copyright Transerv
 */

class FiledownloadController extends App_Operation_Controller
{
    /**
     * Overrides Zend_Controller_Action::init()
     *
     * @access public
     * @return void
     */
    public function init(){
        // init the parent
        parent::init();
    }
    
    
    public function indexAction(){

        set_time_limit(300);
        $this->title = 'File download';
        
        $filename = $this->_getParam('file');
        $path = $this->_getParam('path');
       
        if( $path == ''){
            $filepath = UPLOAD_PATH;
        }
        else if ($path == 'amul_customer'){
            $filepath = UPLOAD_PATH_KOTAK_AMUL_DOC;
        }
        else{
            $filepath = UPLOAD_PATH_CUSTOMER_PHOTO;
        }
        
        $file = realpath($filepath . '/' .$filename);
        
      
        //echo $file;exit;
        $docModel = new Documents();
        $extension = $docModel->getDocType($filename);
         switch ($extension) {
            case "jpg":
                $extheader = 'image/jpg';
                break;
             case "JPG":
                $extheader = 'image/JPG';
                break;
            case "gif":
                $extheader = 'image/gif';
                break;
            case "bmp":
                $extheader = 'image/bmp';
                break;
            case "pdf":
                $extheader = 'application/pdf';
                break;
     }
        if (file_exists($file)) {
            if (FALSE!== ($handler = fopen($file, 'r')))
            {
                header('Content-Description: File Transfer');
                header("Content-Type: $extheader");
                header('Content-Disposition: attachment; filename='.basename($file));
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: private');
                readfile($file);
                exit;
            }
   
        } else {
         $this->_helper->FlashMessenger(
                    array(
                        'msg-error' => 'Document does not exist',
                          )
                        );
           }
       }
      
}