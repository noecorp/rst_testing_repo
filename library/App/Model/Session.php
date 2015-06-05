<?php

class Session extends App_Model
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
    protected $_name = DbTable::TABLE_OPERATION_USERS;
    
    private $_msg;
    
    
     /*
     * implementing single session only
     */
    public function loginSession($params = array())
    {
        if(CURRENT_MODULE == 'operation')
        {
            $tablename = DbTable::TABLE_OPERATION_USERS;
        }
        elseif(CURRENT_MODULE == 'agent')
        {
            $tablename = DbTable::TABLE_AGENTS;
        }
        elseif(CURRENT_MODULE == MODULE_BANK)
        {
            $tablename = DbTable::TABLE_BANK_USER;
        }
        
        if(isset($tablename))
        {
//            $curLife = App_DI_Container::get('ConfigObject')->session->current_life;
            $sessionId    = Zend_Session::getId();
            $select = $this->_db->select()
                    ->from($tablename, array('id', 'session_id', 'date_updated', 'is_logged', new Zend_Db_Expr('NOW()')))
                    ->where("id = ?", $params['user_id']);
           $row = $this->_db->fetchRow($select);
           
           if($row)
           {
                if(empty($row['session_id']) || $row['session_id'] == $sessionId) 
                {
                    $updArr = array(
                            'session_id'    => $sessionId,
                            'date_updated'  => new Zend_Db_Expr('NOW()'),
                            'is_logged'     => FLAG_YES
                        );
                    $this->_db->update($tablename, $updArr, "id = ".$params['user_id']);
                    return true;
                    
                }
                else // diff sessId
                {
                    $sessionLifeTime = App_DI_Container::get('ConfigObject')->session->current_life;
                    $currdateTime = strtotime(date('Y-m-d H:i:s'));
                    $loggedindateTime = strtotime($row['date_updated']);
                    $diff = $currdateTime - $loggedindateTime;
                    if ($diff < $sessionLifeTime){
                        $this->forceLogout($params);
                        return false;
                    }
                    else { 
                         $updArr = array(
                            'session_id'    => $sessionId,
                            'date_updated'  => new Zend_Db_Expr('NOW()'),
                            'is_logged'     => FLAG_YES
                        );
                    $this->_db->update($tablename, $updArr, "id = ".$params['user_id']);
                    return true;
                         
                         }
                    
                }
           }
        }
        return true;
        
    }
    
    /*
     * implementing single session only, validate Login Session
     */
    public function validateLoginSession($params = array())
    {
        if(CURRENT_MODULE == 'operation')
        {
            $tablename = DbTable::TABLE_OPERATION_USERS;
        }
        elseif(CURRENT_MODULE == 'agent')
        {
            $tablename = DbTable::TABLE_AGENTS;
        }
        elseif(CURRENT_MODULE == MODULE_BANK)
        {   
            $tablename = DbTable::TABLE_BANK_USER;
        }
        
        if(isset($tablename))
        {
            $curLife = App_DI_Container::get('ConfigObject')->session->current_life;
            $user = Zend_Auth::getInstance()->getIdentity();
            $params = array('session_id'    => Zend_Session::getId(),
                            'user_id'       => $user->id,
                            'is_logged'     => FLAG_YES);
                
            $select = $select = $this->_db->select()
                    ->from($tablename, array('id', 'session_id', 'date_updated', 'is_logged', new Zend_Db_Expr('NOW()') ))
                    ->where("id = ?", $params['user_id']);
           $row = $this->_db->fetchRow($select);

           if($row)
           {
               if($row['is_logged'] == FLAG_YES && $row['session_id'] == $params['session_id'] ) 
               {
                   if(Util::dateDiff($row['date_updated'], $row['NOW()']) <= $curLife ) {
                        $this->updateLoggedInSession($params); 
                        return true;
                   }
                   else {
                       $this->forceLogout($params);
                       return false;
                   }
               }
               else // i.e. if(is_logged == no || sessId != params->sessiId)
               {
                   $this->forceLogout($params);
                   return false;
               }
           }
           
        }
        return false;
        
    }
    
     /*
     * implementing updating at the time of login for single session only
     */
    public function updateLoggedInSession($params = array())
    {
        if(CURRENT_MODULE == 'operation')
        {
            $tablename = DbTable::TABLE_OPERATION_USERS;
        }
        elseif(CURRENT_MODULE == 'agent')
        {
            $tablename = DbTable::TABLE_AGENTS;
        }
        elseif(CURRENT_MODULE == MODULE_BANK)
        {
            $tablename = DbTable::TABLE_BANK_USER;
        }
        
        if(isset($tablename))
        {
            if(!isset($params['user_id']) || $params['user_id'] == ''){
                $user = Zend_Auth::getInstance()->getIdentity();
                $params['user_id']       = $user->id;
            }
            $params['session_id']    = Zend_Session::getId();

            if(isset($params['is_logged']) && $params['is_logged'] == FLAG_NO)
            {
                $isLogged = FLAG_NO;
                $sessionId = '';
            }
            else 
            {
                $isLogged = FLAG_YES;
                $sessionId = $params['session_id'];
            }
            $updArr = array(
                'session_id'    => $sessionId,
                'date_updated'  => new Zend_Db_Expr('NOW()'),
                'is_logged'     => $isLogged
            );
            if(isset($params['user_id']) && $params['user_id'] != ''){
                $this->_db->update($tablename, $updArr, "id = ".$params['user_id']);
            }
            

        }
    }
    
     /*
     * implementing updating at the time of force logout for single session only
     */
    public function forceLogout($params = array())
    {
        $params['is_logged'] = FLAG_NO;
        $this->updateLoggedInSession($params);

        $this->updateLoginLog( $this->session->login_log_id);
        
        // log the user out
        Zend_Auth::getInstance()->clearIdentity();
        
        // destroy the session
        Zend_Session::destroy();
        
        // go to the login page
        //$this->_redirect($this->formatURL('/profile/login/'));
    }
    
     /*
     * implementing updating at the time of force logout for single session only
     */
    public function logoutSession()
    {
        $params['is_logged'] = FLAG_NO;
        $this->updateLoggedInSession($params);
        
    }
    

   // copy of AgentUser::updateLoginLog
    public function updateLoginLog($lastid)
    {
        $sessionId = Zend_Session::getId();
        $userModel = new BaseUser();
         if($lastid != NULL && $lastid > 0) {
        
             if(CURRENT_MODULE == 'operation')
        {
             $logindata =  array('portal'=> MODULE_OPERATION,'ops_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
        }
        elseif(CURRENT_MODULE == 'agent')
        {
             $logindata =  array('portal'=> MODULE_AGENT,'agent_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
       
        }
        elseif(CURRENT_MODULE == MODULE_BANK)
        {
             $logindata =  array('portal'=> MODULE_BANK ,'bank_id' => $user->id,'datetime_logout' => new Zend_Db_Expr('NOW()'),'comment_username'=> STATUS_SUCCESS,'comment_password'=> STATUS_SUCCESS,'session_id' =>$sessionId);  
       
        }
        
           $userModel->insertLoginLog($logindata);    
         }
    }
  
            
}