<?php

class Remit_Ratnakar_NeftRequestSearchForm extends App_Operation_Form
{
    public function init()
    {  
        $this->_cancelLink = false;
        
        $itemsPerPage = Util::getRecordsPerPage();
        
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'From *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'To *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
        
        $amount = new Zend_Form_Element_Text('amount');
        $amount->setOptions(
            array(
                'label'      => 'Amount',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits',
                                ),
                'maxlength' => '11', 
                'style'     => 'width:200px;'
            )
        );
        $this->addElement($amount);
        
        $duration = $this->addElement('select', 'items_per_page', array(
            'filters'    => array('StringTrim'),
            'required'   => false,
            'label'      => 'Items Per Page',
            'style'     => 'width:200px;',
            'multioptions'     => $itemsPerPage,
        ));
        
        $submit = new Zend_Form_Element_Submit('sub');
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
