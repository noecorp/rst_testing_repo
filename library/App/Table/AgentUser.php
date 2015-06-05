<?php


class App_Table_AgentUser extends App_Table
{
    /**
     * Store the primary group of the user
     *
     * @var App_Table_Group
     */
    public $group;
    
    /**
     * Store the related groups
     *
     * @var array App_Table_Group
     */
    public $groups;
    
    /**
     * Store an array of the related group names
     *
     * @var array
     */
    public $groupNames;
    
    /**
     * Store an array of related group ids
     *
     * @var string
     */
    public $groupIds;
    
    protected $_name = DbTable::TABLE_AGENTS;
    
    
    
    
    
    
    public function signupAgent($param, $agn_info)
    {               
        $mobNum = $param['mobile_number'];
        
        
        $select = $this->select()
                //->from("t_agents")
                ->where('mobile1 <>?',$param['mobile_number'])
                ->where('email =?',$param['email']);
                $agntData = $this->fetchAll($select);
        
        
        
        if(!empty($agntData) && $agntData->count() >0) {
            return 'email_mobile_dup';
        }        
         
         
         $id = $this->insert($param);
         $agn_info['agent_id']=$id;
         $this->_name = DbTable::TABLE_AGENT_DETAILS;
         
         if($id>0){     // adding agent details to t_agent_details
            $this->insert($agn_info);         
         }
         return 'success';         
    }
}