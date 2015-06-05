<?php
/**
 * Form for adding new privileges in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class PrivilegeForm extends App_Operation_Form
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
        $flagIdOptions = $flagModel->findPairs();
        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Name',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength' => 40,
            )
        );
        $this->addElement($name);
        
        $flagId = new Zend_Form_Element_Select('flag_id');
        $flagId->setOptions(
            array(
                'label'      => 'Flag',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'multiOptions' => $flagIdOptions,
            )
        );
        $this->addElement($flagId);
        
        $description = new Zend_Form_Element_Text('description');
        $description->setOptions(
            array(
                'label'      => 'Description',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength' => 200,
            )
        );
        $this->addElement($description);
        
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
                'label'      => 'Save Privilege',
                'required'   => FALSE,
                'title'       => 'Save Privilege',
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
     */
    public function populate($data){
        if (isset($data['id']) && is_numeric($data['id'])) {
            $element = $this->getElement('flag_id');
            $options = $element->getMultiOptions();
            unset($options[$data['id']]);
            $element->setMultiOptions($options);
        }
        parent::populate($data);
    }
}