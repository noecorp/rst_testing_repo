<?php
/**
 * File
 *
 * @package Operation_Models
 * @copyright transerv
 * @author Vikram Singh
 */
class Files extends App_Model
{
    
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';

    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_FILES;
    
    protected $file;
    protected $filename;
    protected $filepath;
    protected $strBatch = '';
    protected $filePermission = '755';
    protected $downloadfilename;
    
    /*
     * function to create txt file
     */
    public function generate($createTxtFile=true)
    {
        $filePath = $this->getFile();
        $fileName = $this->getFilename();
        $filePathwithName = $filePath.'/'.$fileName;
        
        if($createTxtFile) {
           if(!file_exists($filePathwithName)){
                $fp = fopen($filePathwithName,"w");
                fwrite($fp,$this->getBatchString());
                fclose($fp);
                if($this->filePermission!='')
                    chmod($filePath, $this->filePermission);
            }else{
                //echo $this->getBatchString(); exit;
                $fp = fopen($filePathwithName,"w");
                fwrite($fp,$this->getBatchString());
                fclose($fp);
                if($this->filePermission!='')
                    chmod($filePath, $this->filePermission);
                
            }
        }
    }
    
    public function download() {
       $filePath = $this->getFile();        
       $fileName = $this->getFilename();
       $filePathwithName = $filePath . '/'.$fileName;
               
       if(file_exists($filePath)) {
            header("Cache-Control: public");
            header("Content-Description: $this->getFilename()");
            header("Content-Length: ". filesize($filePathwithName).";");
            header("Content-Disposition: attachment; filename= ".$this->getFilename());
            header("Content-Type: text/plain; "); 
            readfile($filePathwithName);
       }
    }
    
    /*
     * function to convert array to string that needs to be written in the txt file
     */
    public function setBatch(array $batchRecords, $glue = SEPARATOR_PIPE)
    {
        $this->strBatch='';
        foreach($batchRecords as $arr)
        {
            $this->strBatch .= implode($glue, $arr);
            $this->strBatch .= "\r\n";
        }
    }
    
    public function getBatchString()
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
    
    
    public function setFilename($filename)
    {
       $this->filename = $filename; 
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
    
     public function getListByLabel($label, $page = 1, $paginate = TRUE) {
        $sql = $this->select()
                ->where('label=?', $label)
                ->where('status=?', STATUS_ACTIVE)
                ->order('date_created DESC');  
         if ($paginate) {
            return $this->_paginate($sql, $page, $paginate);
        } else {
            return $this->fetchAll($sql);
        }
    }
    
    public function getFileInfo($id) {
        $sql = $this->select()
                ->where('id=?', $id)
                ->where('status=?', STATUS_ACTIVE);
            return $this->fetchRow($sql);
    }
   
    public function setDownloadFilename($filename){
       $this->downloadfilename = $filename; 
    }
    
    public function getDownloadFilename()
    {
       return $this->downloadfilename; 
    }
    
    public function downloadFile() {
       $filePath = $this->getFile();        
       $fileName = $this->getFilename();
       $downloadfilename = $this->getDownloadFilename();
       $filePathwithName = $filePath . '/'.$fileName;
               
       if(file_exists($filePath)) {
            header("Cache-Control: public");
            header("Content-Description: $this->getFilename()");
            header("Content-Length: ". filesize($filePathwithName).";");
            header("Content-Disposition: attachment; filename= ".$downloadfilename);
            header("Content-Type: text/plain; "); 
            readfile($filePathwithName);
       }
    }
    
    //inserts data into table and returns last insert id (file id)
    public function insertFileInfo($data) {
        $this->save( $data );
        $lastId = $this->_db->lastInsertId(DbTable::TABLE_FILES, 'id');
        return $lastId;
    }
    
    public function downloadCSVFile()
    {
        $filePath = $this->getFile();        
        $fileName = $this->getFilename();
        $filePathwithName = $filePath . '/'.$fileName;

        if(file_exists($filePath)) {
            header("Cache-Control: public");
            header("Content-Description: $fileName");
            header("Content-Length: ". filesize($filePathwithName).";");
            header("Content-Disposition: attachment; filename= ".$fileName);
            header('Content-Type: application/csv');
            readfile($filePathwithName);
        }
    }
    
    public function getRemittanceReportByLabel($label, $page = 1, $paginate = TRUE) {
        //Enable DB Slave
        $this->_enableDbSlave();
        $sql = $this->select()
                ->from($this->_name, array('file_name', 'params', 'status'))
                ->where('label=?', $label)
                ->where('status IN ("'.STATUS_ACTIVE.'" , "'.STATUS_PENDING.'" , "'.STATUS_STARTED.'")')
                ->order('date_created DESC');  

        $result = $this->_paginate($sql, $page, $paginate);
        //Disable DB Slave
        $this->_disableDbSlave();
        
        return $result;
    }
}