<?php
/**
 * OutputFile Model
 * 
 *
 * @category App
 * @package App_Model
 * @copyright company
 * @author Mini
  */

abstract class OutputFile extends App_Model
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
            }else{
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
            header("Content-Description: Output File");
            header("Content-Length: ". filesize($filePath).";");
            header("Content-Disposition: attachment; filename= ".$this->getFilename());
            header("Content-Type: text/plain; "); 
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
            $arr = str_replace('"', '', $arr);
            $this->strBatch .= implode($glue, $arr);
            $this->strBatch .= "\r\n";
        }
       return $this->strBatch;
    }
 /*
     * function to convert array to string that needs to be written in the CSV file
     */
    
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
    
    
    public function setFilename($filename,$fileExt='.txt')
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