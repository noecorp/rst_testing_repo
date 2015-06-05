<?php
/**
 * Change status form for cardholder active/inactive
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_StatusDeactiveCardholderForm extends App_Agent_Form
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
      
        $remarks = new Zend_Form_Element_Textarea('remarks');
        $remarks->setOptions(
            array(
                'label'      => 'Add your remarks *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
               'style' => 'height:100px;width:300px;',
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 100)),
                                ),
            )
        );
        $this->addElement($remarks);
        
        
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
                'label'      => 'Yes, Deactivate it',
                'required'   => FALSE,
                'title'       => 'Yes, Deactivate it',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'required' => false,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
        
        
        
        
        $medi_assist_id = new Zend_Form_Element_Hidden('medi_assist_id');
        $medi_assist_id->setOptions(
            array(
                'required' => false,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($medi_assist_id);
        
        $employer_name = new Zend_Form_Element_Hidden('employer_name');
        $employer_name->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($employer_name);
        
        $card_number = new Zend_Form_Element_Hidden('card_number');
        $card_number->setOptions(
            array(
                'required' => false,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($card_number);
        
        $mobile = new Zend_Form_Element_Hidden('mobile');
        $mobile->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($mobile);
        
        $email = new Zend_Form_Element_Hidden('email');
        $email->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($email);
        
        $aadhaar_no = new Zend_Form_Element_Hidden('aadhaar_no');
        $aadhaar_no->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($aadhaar_no);
        
        $pan = new Zend_Form_Element_Hidden('pan');
        $pan->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($pan);
        
        $employee_id = new Zend_Form_Element_Hidden('employee_id');
        $employee_id->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($employee_id);
        
        $csrfhash = new Zend_Form_Element_Hidden('csrfhash');
        $csrfhash->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($csrfhash);
        
        $formName = new Zend_Form_Element_Hidden('formName');
        $formName->setOptions(
            array(
                'required' => false,
            )
        );
        $this->addElement($formName);
        
        
        
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


