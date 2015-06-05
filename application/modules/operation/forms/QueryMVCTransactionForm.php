<?php

class QueryMVCTransactionForm extends App_Operation_Form
{
    protected $_cancelLink = false;
    
    public function init()
    {       
        $mobile_number = $this->addElement('text', 'MobileNumber', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Mobile Number: * (+91)',
            'style'     => 'width:200px;',
            'maxlength' => '10',
        ));
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('fromDate',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'From Date: (e.g. dd-mm-yyyy) *',
            'style'     => 'width:200px;',)

        ));
        
        $from = $this->addElement('text', 'fromTime', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(8, 8)),),
            'required'   => true,
            'label'      => 'From Time: (hh:mm:ss)  *',
            'style'     => 'width:200px;',
            'maxlength' => '8',
            'value'     => '00:00:00'
        ));
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('toDate',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'To Date: (e.g. dd-mm-yyyy) *',
            'style'     => 'width:200px;',)

        ));
        
        $to = $this->addElement('text', 'toTime', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(8, 8)),),
            'required'   => true,
            'label'      => 'To Time: (hh:mm:ss)  *',
            'style'     => 'width:200px;',
            'maxlength' => '8',
            'value'     => '00:00:00'
        ));
        
         $btn = new Zend_Form_Element_Hidden('btn_submit');
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
                'class'     => 'tangerine'
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
