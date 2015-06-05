<?php

/**
 * CSV class manages the export data in CSV file format
 * @package Core
 * @copyright transerv
 */

class CSV extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    //protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
   // protected $_name = 't_agents';
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_AgentUser';
    
    
    /* exportCSV function will export data to csv file(on fly) and download that file.
     * it will accept data array, columns name array and file name
     */
    
     public function export($data, $columns, $file='export_report')
     {
         $file .= '.csv';
         if(!empty($columns) && !empty($data)) 
         {

            header('Content-Type: application/csv');
            header('Content-Disposition: attchment; filename='.$file);

            $fp = fopen('php://output', 'w');

            if ($fp && $data) {
                fputcsv($fp, $columns);
                fputcsv($fp, array());
                foreach ($data as $key=>$row) {
                        fputcsv($fp, $row);
                }

               // readfile($file);
                fclose($fp);
                //flush();
          }
          return true;
        }
       else {
              throw new Exception('CSV input data missing!');
            }
     }
   
   /* exportCSV function will export data to csv file(on fly) and download that file.
     * it will accept data array, columns name array and file name
     */
    
     public function exportSpecial($data, $file='export_report')
     {
         $file .= '.csv';
         if(!empty($data)) 
         {

            header('Content-Type: application/csv');
            header('Content-Disposition: attchment; filename='.$file);

            $fp = fopen('php://output', 'w');

            if ($data) {
               foreach ($data as $fields) {
                  fputcsv($fp, $fields);
                }

               // readfile($file);
                fclose($fp);
                //flush();
          }
          return true;
        }
       else {
              throw new Exception('CSV input data missing!');
            }
     }
     
      
    /* getCRNList function will fetch data from crn txt file and return data array after converting in array
     * it will accept crn file name
     */
    
     public function getCRNList($fileName)
     {
         
         $filePath  = UPLOAD_IMPORTCRN_PATH.'/'.$fileName;
         $crnData=array();
         
         if(file_exists($filePath)){
            //$contents = file_get_contents($filePath);
            $ecsCrnTableFields =  Zend_Registry::get('TABLE_ECS_CRN_FIELDS');
            
            $fp = fopen( $filePath, "r" ) or die("Couldn't open $filePath");
            $mainArray = array();
            while ( ! feof( $fp ) ) {
               $line = fgets( $fp, 1024 );
               $lineArray = explode('|', $line);
               if(count($lineArray) == ECS_CRN_FILE_COLUMN_COUNT) {
                   $arr = array();//Reset Every time
                   $arr['crn']              = $lineArray[0];
                   $arr['relation']         = $lineArray[1];
                   $arr['product']          = $lineArray[2];
                   $arr['promotion']        = $lineArray[3];
                   $arr['branch']           = $lineArray[4];
                   $arr['statement_plan']   = $lineArray[5];
                   $arr['transaction_plan'] = $lineArray[6];
                   $arr['embossed_line3']   = $lineArray[7];
                   $arr['embossed_line4']   = $lineArray[8];
                   $arr['extra']            = $lineArray[9];
                   $mainArray[] = $arr;
               }
            }
            return $mainArray;
            //exit('END');
            
            //echo "<pre>";print_r($contents);exit;
            $contentsInArray = explode('|', $contents);
            
            $i=1;
            $j=0;
            $k=0;
            foreach($contentsInArray as $key=>$value){
                //print $key . ' | ' . $value.'<br />';
                if($i==11){
                    $i=1;
                    $k=0;
                    $j++;
                }
                $fieldName = $ecsCrnTableFields[$k];
                //if($k==1 && $value=='')
                  //  $value=STATUS_FREE;
                
                $crnData[$j][$fieldName] = trim($value);
               // echo $value.'<br>';
                
                $i++;
                $k++;
            } 
            exit;
            //echo '<pre>';
            //print_r($crnData);
            //die;
            return $crnData;
         }  else {
                    echo 'file not found';
                  }
            
     }
}