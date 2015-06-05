<?php

class Corp_Boi_TpmisReportForm extends App_Agent_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        $user = Zend_Auth::getInstance()->getIdentity();  
        $mobile1 = new Zend_Form_Element_Text('tp_name');
        $mobile1->setOptions(
            array(
                'label'      => 'Training Partner',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(1, 60)),
                                ),
               
            )
        );
        $this->addElement($mobile1);
        $mobile1->setAttrib('readonly', true);  
        
        $product = new Zend_Form_Element_Text('agent_code');
        $product->setOptions(
            array(
                'label'      => 'BC Code',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '50',
            )
        );
        $this->addElement($product);  

        $product = new Zend_Form_Element_Text('account_no');
        $product->setOptions(
            array(
                'label'      => 'Account No.',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '25',
            )
        );
        $this->addElement($product);  
        
        $product = new Zend_Form_Element_Text('aadhaar_no');
        $product->setOptions(
            array(
                'label'      => 'Aadhaar No.',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '50',
            )
        );
        $this->addElement($product); 
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Wallet Load From: (e.g. dd-mm-yyyy)',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Wallet Load To: (e.g. dd-mm-yyyy)',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
        
        $btn = new Zend_Form_Element_Hidden('sub');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
        $this->addElement($btn);
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required' => false,
                'ignore'   => true,
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
        $this->setDecorators(array(
            'FormElements',
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
     
  
}
?>
