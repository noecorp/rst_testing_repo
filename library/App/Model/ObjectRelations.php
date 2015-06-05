<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */
class ObjectRelations extends App_Model {

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
    protected $_name = DbTable::TABLE_BIND_OBJECT_RELATION;
    
    
    public function getRelationTypeId($lable) {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES, array('id'))
                ->where('label=?',$lable)
                ->limit(1);
        $rs =  $this->_db->fetchRow($sql);
        $rs = Util::toArray($rs);
        if(!empty($rs) && isset($rs['id'])) {
            return $rs['id'];
        }
        return false;
    }
    
    
    public function getFromObjectInfo($to, $label) {
        $sql = $this->_db->select()
                ->from($this->_name." as ob",array('from_object_id','to_object_id','object_relation_type_id','date_start','date_end','comment','status'))
                ->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", 'ob.object_relation_type_id = obt.id',array('label'))
                ->where('ob.to_object_id=?',$to)
                ->where('obt.label=?',$label)
                ->where('ob.status=?',STATUS_ACTIVE);
        $rs = $this->_db->fetchRow($sql);
        return $rs;
    }
    
    
    public function checkRelation($to, $from, $label) {
        $sql = $this->_db->select()
                ->from($this->_name." as ob",array('from_object_id','to_object_id','object_relation_type_id','date_start','date_end','comment','status'))
                ->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", 'ob.object_relation_type_id = obt.id',array('label'))
                ->where('ob.to_object_id=?',$to)
                ->where('obt.label=?',$label)
                ->where('ob.from_object_id=?',$from)
                ->where('ob.status=?',STATUS_ACTIVE);
        $rs = $this->_db->fetchRow($sql);
        if(!empty($rs)) {
            return TRUE;
        }
        return FALSE;
    }
    
     public function getListByLabelPaginator($label, $page = 1, $paginate = NULL, $force = FALSE) {
           $sql = $this->_db->select()
                ->from($this->_name." as ob",array('from_object_id','to_object_id','object_relation_type_id','date_start','date_end','comment','status'))
                ->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", 'ob.object_relation_type_id = obt.id',array('label'))
                ->where('obt.label=?',$label)
                ->where('ob.status=?',STATUS_ACTIVE);
            return $this->_paginate($sql, $page, $paginate);
    }
    
    public function getLabelId($label) {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES, array('id'))
                ->where('label=?',$label)
                ->limit(1);
        $rs= $this->_db->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return false;
    }

    
    public function insertWithLabel($param, $label) {
        $rs = $this->getLabelId($label);
        $rs = Util::toArray($rs);
        if(empty($rs) || !isset($rs['id'])) {
            throw new App_Exception('Label not found');
        }
        if(!isset($param['status'])) {
            $param['status'] = STATUS_ACTIVE;
        }
        if(!isset($param['comment'])) {
            $param['comment'] = '';
        }
        if(!isset($param['object_relation_type_id'])) {
            $param['object_relation_type_id'] = $rs['id'];
        }
        return $this->insert($param);
    }
    
    
    public function dateCheckUsed($label, $startDate='',$endDate='') {
        $rsInfo = $this->getLabelId($label);
        if(empty($rsInfo)) {
            throw new App_Exception('Label Not found');
            //return;
        }
        if(!empty($startDate)) {
            $sql = $this->select()
                    ->where('object_relation_type_id=?',$rsInfo['id'])
                    ->where('date_start <= ?', $startDate)
                    ->where('date_end >= ?', $startDate);
            $rs =  $this->_db->fetchRow($sql);                
            if(!empty($rs)) {
                return FALSE;
            }
        }
        if(!empty($endDate)) {
            $sql = $this->select()
                    ->where('object_relation_type_id=?',$rsInfo['id'])
                    ->where('date_start <= ?', $endDate)
                    ->where('date_end >= ?', $endDate);
            $rs =  $this->_db->fetchRow($sql);                
            if(!empty($rs)) {
                return FALSE;
            }
        }
        return TRUE;
    }    
    public function getLabelById($id) {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES)
                ->where('id =?',$id)
                ->limit(1);
        $rs= $this->_db->fetchRow($sql);                
        if(!empty($rs)) {
            return $rs;
        }
        return false;
    }
    
     public function getToObjectInfo($from, $label, $all = FALSE) {
        $sql = $this->_db->select()
                ->from($this->_name." as ob",array('from_object_id','to_object_id','object_relation_type_id','date_start','date_end','comment','status'))
                ->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", 'ob.object_relation_type_id = obt.id',array('label'))
                ->where('ob.from_object_id=?',$from)
                ->where('obt.label=?',$label)
                ->where('ob.status=?',STATUS_ACTIVE);
        
        if($all == TRUE) {
            return $this->_db->fetchAll($sql);
        } else {
            return $this->_db->fetchRow($sql);
        }
    }
    
     public function getToObjectDetails($from,$to, $labelID) {
        $sql = $this->_db->select()
                ->from(DbTable::TABLE_BIND_OBJECT_RELATION." as ob",array('from_object_id','to_object_id','object_relation_type_id','date_start','date_end','comment','status'))
                ->where('ob.from_object_id=?',$from)
                ->where('ob.to_object_id=?',$to)
                ->where('ob.object_relation_type_id =?',$labelID)
                ->where('ob.status=?',STATUS_ACTIVE);
       
        $rs = $this->_db->fetchRow($sql);
        return $rs;
    }
    
    /*
     * deleteToObjectInfo will delete the mapping from object_relations table
     */
    public function deleteToObjectInfo($to, $label) {
        $rs = $this->getLabelId($label);
        $where = "to_object_id = ". $to. " AND object_relation_type_id = '".$rs['id'] ."'";
        $this->_db->delete(DbTable::TABLE_BIND_OBJECT_RELATION, $where);
        return true;
    }
    
    public function getToObjectInfoAgent($from, $label, $all = FALSE) {
        $sql = $this->_db->select();
        $sql->from($this->_name." as ob",array('from_object_id','to_object_id','object_relation_type_id','date_start','date_end','comment','status'));
        $sql->joinLeft(DbTable::TABLE_BIND_OBJECT_RELATION_TYPES . " as obt", 'ob.object_relation_type_id = obt.id',array('label'));
        $sql->join(DbTable::TABLE_AGENTS . " as a",'ob.to_object_id = a.id',array('id','first_name','last_name','agent_code'));
        $sql->where('ob.from_object_id=?',$from);
        $sql->where('obt.label=?',$label);
        $sql->where('ob.status=?',STATUS_ACTIVE);
        $sql->where('a.status=?',UNBLOCKED_STATUS);
        if($all == TRUE) {
            return $this->_db->fetchAll($sql);
        } else {
            return $this->_db->fetchRow($sql);
        }
    }
}