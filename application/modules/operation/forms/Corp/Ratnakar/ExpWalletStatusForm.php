<?php

class Corp_Ratnakar_ExpWalletStatusForm extends App_Operation_Form
{
    
    public function init()
    { 
        $this->_cancelLink = false;
        
         //get bank unicode
        $bankPaytronic = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
        $productPatUnicode = $bankPaytronic->bank->unicode;
        
        //get bank id using bank unicode
        $objBankk = new Banks();
        $bankInfo = $objBankk->getBankbyUnicode($productPatUnicode);
        $rat_bank_id = $bankInfo['id'];
        
        $productModel = new Products();
        $productOptionsArr = $productModel->getRatProgramProducts($bankId = $rat_bank_id, PROGRAM_TYPE_CORP);
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
        
        
        $durationArr = Util::getDuration();
       
        $purse = new Zend_Form_Element_Select('purse_master_id');
        
        $purse->setOptions(
            array(
                'label'      => 'Wallet *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'multioptions'    => array('' =>'Select Wallet'),  
            )
        );
        $purse->setRegisterInArrayValidator(false);
        $this->addElement($purse);
       
        $batch = new Zend_Form_Element_Select('batch_name');
        $batch->setOptions(
            array(
                'label'      => 'Batch Name *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select Batch Name'),
                'style' => 'width:210px;',
            )
        );
        $batch->setRegisterInArrayValidator(false);
        $this->addElement($batch);    
        
        $btn = new Zend_Form_Element_Hidden('batch');
        $btn->setOptions(
            array(
            )
        );
         $this->addElement($btn);
        $btn = new Zend_Form_Element_Hidden('purse');
        $btn->setOptions(
            array(
            )
        );
        $this->addElement($btn);
        
        
        
       
        
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
