<?php

class Corp_Boi_TpmisReportForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
                
        $product = new Zend_Form_Element_Text('ref_num');
        $product->setOptions(
            array(
                'label'      => 'AOF Ref No.',
               
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
