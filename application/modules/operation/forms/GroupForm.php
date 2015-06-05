<?php
/**
 * Form for adding new user groups in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class GroupForm extends App_Operation_Form
{
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        
        //$this->_cancelLink = false;
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
 
        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
//                                    $uniqueGroupNameValidator,
                                ),
            )
        );
        $this->addElement($name);
        

        
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save User Group',
                'required'   => FALSE,
                'title'       => 'Save User Group',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                   
        ));
               
        $this->setDecorators(array(
            'FormElements',
          
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
    
    /**
     * Overrides populate() in App_Form
     * 
     * @param mixed $data 
     * @access public
     * @return void
     * /
    public function populate($data){
        if (isset($data['id']) && is_numeric($data['id'])) {
            $element = $this->getElement('parent_id');
            $options = $element->getMultiOptions();
            unset($options[$data['id']]);
            $element->setMultiOptions($options);
        }
        
        parent::populate($data);
    }*/
    
    /**
     * Overrides isValid() in App_Form
     * 
     * @param array $data 
     * @access public
     * @return bool
     * /
    public function isValid($data){
        if (isset($data['id']) && is_numeric($data['id'])) {
            $this->getElement('name')
                 ->getValidator('Zend_Validate_Db_NoRecordExists')
                 ->setExclude(array('field' => 'id',
                                    'value' => $data['id']));
        }
        
        return parent::isValid($data);
    }*/
}