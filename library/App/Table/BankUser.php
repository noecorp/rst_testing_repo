<?php


class App_Table_BankUser extends App_Table
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
    
    protected $_name = DbTable::TABLE_BANK_USER;
    
    
    
    
    
}