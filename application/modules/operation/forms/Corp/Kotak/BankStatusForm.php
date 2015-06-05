<?php

class Corp_Kotak_BankStatusForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
       
        
        $kotakModel = new Corp_Kotak_Customers();
        $productList = $kotakModel->corpProductList();
        
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Select Product *',
                'multioptions'    => $productList,
                            
                       
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
               
        
         $statelist = new CityList();
         $stateOptionsList = $statelist->getStateList($countryCode = 356);      
        
        $res_state = new Zend_Form_Element_Select('state');
        $res_state->setOptions(
            array(
                'label'      => 'State ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateOptionsList,                       
            )
        );
        $this->addElement($res_state);
       
        
       
     $name = new Zend_Form_Element_Text('pincode');
        $name->setOptions(
            array(
                'label'      => 'Pincode',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 10)),
                                ),
                'maxlength' => '10',
            )
        );
        $this->addElement($name);
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_approval',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'maxlength'  => 10,
            'label'      => 'Pending Since (e.g. dd-mm-yyyy)',
            'style'      => 'width:200px;',)

        ));

      
        $bankname = new Zend_Form_Element_Select('status');
        $bankname->setOptions(
            array(
                'label'      => 'Application Status',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => Util::getKotakStatusList(),         
            )
        );
        $this->addElement($bankname);
        
      
         
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
