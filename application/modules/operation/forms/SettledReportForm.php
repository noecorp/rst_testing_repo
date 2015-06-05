<?php

class SettledReportForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        $productModel = new Products();

        $product_const_arr = array(PRODUCT_CONST_RAT_HAP,PRODUCT_CONST_RAT_COP,PRODUCT_CONST_RAT_MEDI,PRODUCT_CONST_RAT_SHOP,PRODUCT_CONST_RAT_CNY,PRODUCT_CONST_RAT_GPR,PRODUCT_CONST_RAT_SUR,PRODUCT_CONST_RAT_CTY,PRODUCT_CONST_RAT_HFCI,PRODUCT_CONST_RAT_PAYU,PRODUCT_CONST_RAT_BMS,PRODUCT_CONST_RAT_SMP);

        $productOptionsArr = $productModel->getProductInfoByConstDD($product_const_arr); 
        
        //
       
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Product Name *',
                'multioptions'    => $productOptionsArr,

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
        
       
        $crn = new Zend_Form_Element_Text('card_number');
        $crn->setOptions(
            array(
                'label'      => 'Card Number',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
            )
        );
        $this->addElement($crn);
        
        $cardPackId = new Zend_Form_Element_Text('settlement_ref_no');
        $cardPackId->setOptions(
            array(
                'label'      => 'Settlement Ref No.',

                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
            )
        );
        $this->addElement($cardPackId);
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Debit From: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Debit To: (e.g. dd-mm-yyyy) ',
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
