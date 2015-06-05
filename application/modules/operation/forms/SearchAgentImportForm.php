<?php

class SearchAgentImportForm extends App_Operation_Form
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
        
        $statusArr = Util::getAgentImportStatus();
        
        $filename = new Zend_Form_Element_Text('file_name');
        $filename->setOptions(
            array(
                'label'      => 'File Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($filename);
        
        $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength'  => 100,
            )
        );
        $this->addElement($email);
        
        $phoneNumber = new Zend_Form_Element_Text('mobile');
        $phoneNumber->setOptions(
            array(
                'label'      => 'Mobile Number',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits',
                                ),
                'maxlength'  => 10,
            )
        );
        $this->addElement($phoneNumber);
        
        $distcode = new Zend_Form_Element_Text('distributor_code');
        $distcode->setOptions(
            array(
                'label'      => 'Distributor Code',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits',
                                ),
                'maxlength'  => 11,
            )
        );
        $this->addElement($distcode);
        
        
        $status = new Zend_Form_Element_Select('import_status');
        $status->setOptions(
            array(
                'label'        => 'Status',
                'required'     => false,
                'filters'      => array(
                                    'StringTrim',
                                    'StripTags',
                                  ),
                'validators'   => array(
                                    'NotEmpty',
                                  ),
                'style'        => 'width:210px;',
                'maxlength'    => '100',
                'multioptions' => $statusArr,         
            )
        );
        $this->addElement($status);
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => FALSE,
            'label'      => 'From: (e.g. dd-mm-yyyy)',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => FALSE,
            'label'      => 'To: (e.g. dd-mm-yyyy)',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
        
        $submit = new Zend_Form_Element_Submit('btn_submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required'   => FALSE,
                'title'       => 'Submit',
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
                // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div', 'class' => 'innerbox')),
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
		
    }
}
?>