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
//set_time_limit() has been disabled for security reasons
//        set_time_limit(300);
        $this->title = 'File download';
        
        $filename = $this->_getParam('file');
        $path = $this->_getParam('path');
       
        if( $path == ''){
            $filepath = UPLOAD_PATH;
        }
        else if ($path == 'amul_customer'){
            $filepath = UPLOAD_PATH_KOTAK_AMUL_DOC;
        }
        else if ($path == 'app_file'){
            $filepath = APPLICATION_UPLOAD_PATH;
        }
        else if ($path == 'output_file'){
            $filepath = UPLOAD_BOI_NSDC_PATH;
        }
        else if ($path == 'tp_mis'){
            $filepath = UPLOAD_PATH_BOI_TP_MIS_REPORTS;
        }
        else if ($path == 'corp_doc'){
            $filepath = UPLOAD_PATH_RAT_CORPORATE_DOC;
        }
        else if ($path == 'rat_customer'){
            $filepath = UPLOAD_PATH_CUSTOMER_PHOTO;
        }
        else if ($path == 'kotak_customer'){
            $filepath = UPLOAD_PATH_KOTAK_AMUL_DOC;
        }
        else if ($path == 'rat_cardholder'){
            $filepath = UPLOAD_PATH_RAT_CORP_DOC;
        }else if ($path == 'rat_remit'){
            $filepath = UPLOAD_REMIT_RAT_PATH;
        }else if ($path == 'rat_settlement'){
            $filepath = UPLOAD_CUSTOMER_RATNAKAR_SETTLEMENT;
        }
        else if($path == 'remit_report_file'){
            $filepath = UPLOAD_PATH_REMITTANCE_TRANSACTION_REPORTS;
        }else if($path == 'rbl_recon_file'){
            $filepath = UPLOAD_PATH_RAT_REMIT_TXN_RECON_REPORTS;
        }else if($path == 'kotak_recon_file'){
            $filepath = UPLOAD_PATH_KOTAK_REMIT_TXN_RECON_REPORTS;
        }
        else{
            $filepath = UPLOAD_PATH_CORPORATE_PHOTO;
        }
        
        $file = realpath($filepath . '/' .$filename);
        
      
        //echo $file;exit;
        $docModel = new Documents();
        $extension = $docModel->getDocType($filename);
        $extheader = '';
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
             case "txt":
                $extheader = 'text/plain';
                break;
            case "csv":
                $extheader = 'text/plain';
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