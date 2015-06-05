<?php

class Corp_Ratnakar_PendingKYCForm extends App_Operation_Form
{
    
    public function init()
    { 
        $this->_cancelLink = false;
        
        $bankPaytronic = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $productPatUnicode = $bankPaytronic->bank->unicode;
        
        //get bank id using bank unicode
        $objBankk = new Banks();
        $bankInfo = $objBankk->getBankbyUnicode($productPatUnicode);
        $rat_bank_id = $bankInfo['id'];
        
        $productModel = new Products();
        $productOptionsArr = $productModel->getRatProgramProducts($bankId = $rat_bank_id, PROGRAM_TYPE_CORP, 'all');
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
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

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
