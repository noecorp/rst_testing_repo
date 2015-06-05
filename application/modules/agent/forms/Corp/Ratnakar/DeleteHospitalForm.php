<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_DeleteHospitalForm extends App_Agent_Form
{
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    protected $_cancelLink = true;
    //public $_cancelLinkUrl;
    
    public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
        //$this->setCancelLink($this->$_cancelLinkUrl);
        

        //echo $this->$_cancelLinkUrl; exit;
      
        
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'required' => true,
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
                'label'      => 'Yes, delete it',
                'required'   => FALSE,
                'title'       => 'Yes, delete it',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $hospital_id_code = new Zend_Form_Element_Hidden('hospital_id_code');
        $hospital_id_code->setOptions(
            array(
                'required' => false,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($hospital_id_code);
        
        
        
        
        
        $terminal_id_code = new Zend_Form_Element_Hidden('terminal_id_code');
        $terminal_id_code->setOptions(
            array(
                'required' => false,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($terminal_id_code);
        
        $hospital_name = new Zend_Form_Element_Hidden('hospital_name');
        $hospital_name->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($hospital_name);
        
        $pin_code = new Zend_Form_Element_Hidden('pin_code');
        $pin_code->setOptions(
            array(
                'required' => false,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($pin_code);
        
        $state = new Zend_Form_Element_Hidden('state');
        $state->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($state);
        
        $city = new Zend_Form_Element_Hidden('city');
        $city->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($city);
        
        
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                   
        ));
               
        $this->setDecorators(array(
            'FormElements',
          
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
}


