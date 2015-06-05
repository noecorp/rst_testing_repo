<?php
/**
 * Neft Request Model
 * 
 *
 * @category App
 * @package App_Model
 * @copyright company
 * @author Mini
  */

abstract class NeftRequest extends App_Model
{
    protected $file;
    protected $filename;
    protected $filepath;
    protected $strBatch = '';
    protected $filePermission = '';
    
    /*
     * function to create txt file
     */
    public function createTxtFile($createTxtFile=true, $downloadTxtFile=false)
    {
        $filePath = $this->getFile();
        
        // for create file case
        if($createTxtFile) {
            if(!file_exists($filePath)){
                $fp = fopen($filePath,"w");
                fwrite($fp,$this->getStrBatch());
                fclose($fp);
                if($this->filePermission!='')
                    chmod($filePath, $this->filePermission);
            }
        }
        
        // for download file case
        if($downloadTxtFile) {
            header("Cache-Control: public");
            header("Content-Description: BOI NEFT");
            header("Content-Length: ". filesize($filePath).";");
            header("Content-Disposition: attachment; filename= ".$this->getFilename());
            header("Content-Type: text/plain; "); 
            // header("Content-Transfer-Encoding: binary");
            readfile($filePath);
        }
        
    }
    
    
     public function createXlSFile($createTxtFile=true, $downloadTxtFile=false)
    {
        $filePath = $this->getFile();
        $fileName = $this->getFilename();
        // for create file case
        if($createTxtFile) {
            
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("RBL NEFT")
                                        ->setLastModifiedBy("RBL NEFT")
                                        ->setTitle($fileName)
                                        ->setSubject("RBL NEFT")
                                        ->setDescription("RBL NEFT")
                                        ->setKeywords("RBL NEFT")
                                        ->setCategory("RBL NEFT");
            $records = $this->getStrBatch();
            $num =1;
            foreach($records as $record){
                $alpha='A';
                foreach($record as $colName =>$arrValue) {
                   $objPHPExcel->getActiveSheet(0)->setCellValueExplicit($alpha.$num, $arrValue, PHPExcel_Cell_DataType::TYPE_STRING);
                   $alpha++;
                }
                $num++;
            }
            
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save($filePath);
            
            if($this->filePermission!='')
                chmod($filePath, $this->filePermission);
            
        }
        
        // for download file case
        if($downloadTxtFile) {
            header("Cache-Control: public");
            header("Content-Description: BOI NEFT");
            header("Content-Length: ". filesize($filePath).";");
            header("Content-Disposition: attachment; filename= ".$this->getFilename());
            header("Content-Type: application/vnd.ms-excel;"); 
            // header("Content-Transfer-Encoding: binary");
            readfile($filePath);
        }
        
    }
    
//    abstract public function downloadNeftTxt( );
//    abstract public function createBatchRecords( );
    
    /*
     * function to convert array to string that needs to be written in the txt file
     */
    public function setStrBatch($batchRecords = array(), $glue = SEPARATOR_PIPE)
    {
        $this->strBatch='';
        foreach($batchRecords as $arr)
        {
            $this->strBatch .= implode($glue, $arr);
            $this->strBatch .= "\r\n";
        }
    }
    
    public function setStrBatchXLS($batchRecords = array(), $glue = SEPARATOR_PIPE)
    {
        $this->strBatch=$batchRecords;
        //foreach($batchRecords as $arrRow)
        //{
        //    $this->strBatch .= '<tr>';
        //     $strTd ='';
        //    foreach($arrRow as $colName =>$arrValue) {
        //       if (is_numeric($arrValue)) {
        //        $strTd .= '<td style="mso-number-format:\'\@\'">'.$arrValue.'</td>';
        //        }else{
        //             $strTd .= '<td style="mso-number-format:\'\General\'">'.$arrValue.'</td>'; 
        //        }
        //       
        //    }
        //  
        //    $this->strBatch .= $strTd;
        //     $this->strBatch .= '</tr>';
        //}
        //       $this->strBatch.='</table>';
    }
    
    public function getStrBatch()
    {
       return $this->strBatch; 
    }
    
    public function setFile()
    {
       $this->file = $this->getFilepath() ."/". $this->getFilename();
    }
    
    public function getFile()
    {
       return $this->file; 
    }
    
    
     public function setFilename($filename,$fileExt=".txt")
    {
       $this->filename = $filename .$fileExt; 
    }
    
    public function getFilename()
    {
       return $this->filename; 
    }
    
    public function setFilepath($filepath)
    {
       $this->filepath = $filepath; 
       $this->setFile();
    }
    
    public function getFilepath()
    {
       return $this->filepath; 
    }
    
    public function setFilePermission($filePermission)
    {
       $this->filePermission = $filePermission; 
    }
    
    public function getFilePermission()
    {
       return $this->filePermission; 
    }
}