<?php
/**
 * Model for managing the Flippers in the application.
 *
 * It operates according to the following rules:
 * - each controller is considered a resource
 * - each controller action is considered a privilege
 * - each user group is considered to be a role
 *
 * For details, see http://framework.zend.com/manual/en/zend.acl.introduction.html
 *
 * @package backoffice_models
 * @copyright company
 */

class Flipper extends App_Model
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
    protected $_name = DbTable::TABLE_FLIPPERS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    protected $_rowClass = 'App_Table_Flipper';
    
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    protected $_referenceMap = array(
        'Group' => array(
            'columns' => 'group_id',
            'refTableClass' => 'Group',
            'refColumns' => 'id'
        ),
        'BankGroup' => array(
            'columns' => 'group_id',
            'refTableClass' => 'BankGroup',
            'refColumns' => 'id'
        ),
        'CorporateGroup' => array(
            'columns' => 'group_id',
            'refTableClass' => 'CorporateGroup',
            'refColumns' => 'id'
        ),
        'Flag' => array(
            'columns' => 'flag_id',
            'refTableClass' => 'Flag',
            'refColumns' => 'id'
        ),
        'Privilege' => array(
            'columns' => 'privilege_id',
            'refTableClass' => 'Privilege',
            'refColumns' => 'id'
        ),
    );
    
    /**
     * Finds all the Flippers associated with a certain group
     * 
     * @param int $groupId 
     * @access public
     * @return void
     */
    public function findByGroupId($groupId){
        $select = $this->_select();
        $select->where('group_id = ?', $groupId);
        
        return $this->fetchAll($select);
    }
    
    /**
     * Saves the permissions for a group
     * 
     * @param array $data 
     * @access public
     * @return void
     */
    public function savePermissions($data){
        $this->delete($this->_db->quoteInto('group_id = ?', $data['group_id']));
        
        foreach($data['flipper'] as $resourceId => $privileges){
            foreach($privileges as $privilegeId => $allow){
                if($allow){
                    try{
                        $this->insert(array(
                            'group_id' => $data['group_id'],
                            'flag_id' => $resourceId,
                            'privilege_id' => $privilegeId,
                            'allow' => 1
                        ));
                    }catch(Zend_Exception $ze){
                        // nothing special, just a duplicate key
                        App_Logger::log($e->getMessage(), Zend_Log::ERR);
                    }
                } else {
                    $this->delete(array(
                        'group_id' => $data['group_id'],
                        'flag_id' => $resourceId,
                        'privilege_id' => $privilegeId,
                    ));
                }
            }
        }
    }
    
    /**
     * Deletes all associations with the given privilege id
     * 
     * @param int $privilegeId
     * @access public
     * @return void
     */
    public function deleteByPrivilegeId($privilegeId){
        $this->delete($this->_db->quoteInto('privilege_id = ?', $privilegeId));
    }
    
    public function fetchAllBankFlippers(){
        $select = $this->select()
               ->setIntegrityCheck(FALSE)
                ->from(DbTable::TABLE_FLIPPERS . ' as fl')
                ->join(DbTable::TABLE_FLAGS . ' as f', 'fl.flag_id = f.id','')
                ->where('f.name like "bank-%"');
        
        //echo $select;exit;
        return $this->fetchAll($select);
    }   
    
    public function fetchAllCorporateFlippers(){
        $select = $this->select()
               ->setIntegrityCheck(FALSE)
                ->from(DbTable::TABLE_FLIPPERS . ' as fl')
                ->join(DbTable::TABLE_FLAGS . ' as f', 'fl.flag_id = f.id','')
                ->where('f.name like "corporate-%"');
        
        //echo $select;exit;
        return $this->fetchAll($select);
    } 
    
    public function findByOpsId($opsId, $groupId){
        $select = $this->select()
                    ->setIntegrityCheck(FALSE)
                    ->from($this->_name. ' as flippers', array('flippers.privilege_id','flippers.group_id','flippers.flag_id','flippers.allow'))
                    ->join(DbTable::TABLE_OPERATION_USERS.' as o','o.id = "'.$opsId.'"', array('o.email'))
                    ->join(DbTable::TABLE_OPERATION_USERS_GROUP." as og","o.id=og.user_id",array('og.group_id as ops_group_id'))
                    ->join(DbTable::TABLE_PRIVILEGES.' as p', 'flippers.privilege_id = p.id', array('p.name as privilege_name'))
                    ->join(DbTable::TABLE_FLAGS.' as f', 'flippers.flag_id = f.id', array('f.name as flag_name', 'f.active_on_prod'))
                    ->join(DbTable::TABLE_GROUP.' as g', 'flippers.group_id = g.id', array('g.name as group_name'))
                    ->where('o.status =?',STATUS_ACTIVE)
                    ->where('flippers.group_id = ?', $groupId);
        return $this->fetchAll($select);
    }
}