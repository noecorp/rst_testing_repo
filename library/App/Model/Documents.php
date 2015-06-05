<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class Documents extends App_Model
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
    protected $_name = DbTable::TABLE_DOCS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */  
  public function renameDocs($id,$data){
        
      $update = $this->update($data,'id='.$id);
      return $update;
    }
    
    
    public function updateDocs($docId){
      $data = array('status'=>'inactive') ;
      $update = $this->update($data,'id='.$docId);
      return $update;
    }
    
    
     public function saveAgentDocs($data){
        
      $insert = $this->insert($data);
      return $insert;
    }
    
    public function checkAgentDoc($id,$type){
         $select = $this->select()
                ->where("doc_type='$type'")
                ->where("status='active'")
                ->where("doc_agent_id=$id");
        
        $file = $this->fetchrow($select);
        return $file['id'];
        
    }
    public function getDocType($file){
        
        $select = $this->select()
                ->where("file_name='$file'");
        
        $filename = $this->fetchrow($select);
        return $filename['file_type'];
    }
    /**
     * Overrides findById() in App_Model
     * 
     * @param int $userId 
     * @access public
     * @return array
     */
    public function findById($agentId,$force = false){
        $docs = parent::findById($agentId);
        return $docs;
    }
     public function checkCustomerDoc($id){
         $select = $this->select()
                ->where("id =?",$id);
        
        $file = $this->fetchrow($select);
        return $file;
        
    }
     
    public function saveCustomerDocs($data){
      $insert = $this->insert($data);
      return $insert;
    }
    
    
    public function checkCorporateDoc($id,$type){
         $select = $this->select()
                ->where("doc_type='$type'")
                ->where("status='active'")
                ->where("doc_corporate_id=$id");
        
        $file = $this->fetchrow($select);
        return $file['id'];
        
    }
}