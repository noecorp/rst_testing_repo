<?php

class Corp_Kotak_SampleLoadReportForm extends App_Corporate_Form
{
    
    public function init()
    {   

        $statusArr = Util::getCardLoadStatusList();
        $this->_cancelLink = false;
        
        $durationArr = Util::getDuration();
       
        
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Product Name *',
                'multioptions'    => array('' => 'Select Product'),

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($product);
        
        $department = new Zend_Form_Element_Text('department');
        $department->setOptions(
            array(
                'label'      => 'Department',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                //'validators' => array(
                //                        array('Regex', FALSE, array('pattern' => '/[a-z][A-Z] /')),
                //                     ),
                'maxlength'  => '20',
                
            )
        );
        
        $this->addElement($department);
        
        $location = new Zend_Form_Element_Text('location');
        $location->setOptions(
            array(
                'label'      => 'Location',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                //'validators' => array(
                //                        array('Regex', FALSE, array('pattern' => '/[a-z][A-Z] /')),
                //                     ),
                'maxlength'  => '26',
                
            )
        );
        
        $this->addElement($location);
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'From: (e.g. dd-mm-yyyy) *',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'To: (e.g. dd-mm-yyyy) *',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
        
         
        $prod = new Zend_Form_Element_Hidden('product');
        $prod->setOptions(
            array(
            )
        );
        $this->addElement($prod);
        
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