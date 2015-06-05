<?php

class Corp_Boi_TpmisGenericReportForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        $product = new Zend_Form_Element_Text('tp_mobile');
        $product->setOptions(
            array(
                'label'      => 'TP Mobile',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(10, 10)),
                                ),
                    'maxlength' => '10',  
            )
        );
        $this->addElement($product); 
        
        
        $product = new Zend_Form_Element_Text('agent_mobile');
        $product->setOptions(
            array(
                'label'      => 'Agent Mobile',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(10, 10)),
                                ),
                'maxlength' => '10',  
            )
        );
        $this->addElement($product); 
         
        
        $product = new Zend_Form_Element_Text('tp_code');
        $product->setOptions(
            array(
                'label'      => 'TP Code',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(12, 12)),
                                ),
                    'maxlength' => '12',  
            )
        );
        $this->addElement($product);
        
        $product = new Zend_Form_Element_Text('agent_code');
        $product->setOptions(
            array(
                'label'      => 'Agent Code',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(12, 12)),
                                ),
                    'maxlength' => '12',  
            )
        );
        $this->addElement($product); 
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('wallet_load_from',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => TRUE,
            'label'      => 'Wallet Load From',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('wallet_load_to',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => TRUE,
            'label'      => 'Wallet Load To',
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
