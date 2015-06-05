<?php
/**
 * Form for editing groups per user
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class GroupPermissionsForm extends App_Operation_Form
{
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
        
        $flagModel = new Flag();
        $flags = $flagModel->getAllFlagsAndPrivileges();
        //echo "<pre>";print_r($flags);exit;
        foreach ($flags as $flag) {
            $displayGroup = array();
            foreach ($flag->privileges as $privilege) {
                $checkbox = new Zend_Form_Element_Checkbox('flipper_' . $flag->id . '_' . $privilege->id);
                /*$checkbox->setOptions(
                    array(
                        'label' => '/' . $flag->name . '/' . $privilege->name . '/ (' . $privilege->description . ')',
                    )
                );*/
                $checkbox->setOptions(
                    array(
                        'label' =>  $privilege->description .' (/'. $flag->name . '/' . $privilege->name .')',
                    )
                );
                $this->addElement($checkbox);
                $displayGroup[] = $checkbox->getName();
            }
            $displayGroupTitle = ucfirst($flag->name) . ' (' . $flag->description . ')';

            if(!empty($displayGroup)) {
            $this->addDisplayGroup(array_values($displayGroup), $flag->name)
                 ->getDisplayGroup($flag->name)
                 ->setLegend($displayGroupTitle);
            }
        }
        //echo $flag->name;exit;
        $groupId = new Zend_Form_Element_Hidden('group_id');
        $groupId->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($groupId);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Permissions',
                'required'   => FALSE,
                'title'       => 'Save Permissions',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column-wide')),
                   
                   
        ));
               
        $this->setDecorators(array(
            'FormElements',
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
    
    /**
     * Overrides populate() in Zend_Form
     * 
     * @param array $data 
     * @access public
     * @return void
     */
    public function populate($data, $id){
        $parsed = array('group_id' => $id);
        
        foreach ($data as $flipper){
            if($flipper['allow']){
                $parsed['flipper_' . $flipper['flag_id'] . '_' . $flipper['privilege_id']] = 1;
            }
        }
        parent::populate($parsed);
    }
    
    /**
     * Overrides getValues() in Zend_Form
     * 
     * @access public
     * @return array
     */
    public function getValues(){
        $raw = parent::getValues();
        $values = array(
            'group_id' => $raw['group_id'],
            'flipper' => array(
            ),
        );
        
        foreach ($raw as $key => $value) {
            if (preg_match('/^flipper_([0-9]{1,})_([0-9]{1,})$/', $key)) {
                $parts = explode('_', $key);
                if (!isset($values['flipper'][$parts[1]])) {
                    $values['flipper'][$parts[1]] = array();
                }
                $values['flipper'][$parts[1]][$parts[2]] = $value;
            }
        }
        
        return $values;
    }
}