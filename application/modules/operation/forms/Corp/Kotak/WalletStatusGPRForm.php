<?php

class Corp_Kotak_WalletStatusGPRForm extends App_Operation_Form
{
    
    public function init()
    { 
        $this->_cancelLink = false;
        
        
        //**//
        $durationArr = Util::getDuration();
        $purseList = new MasterPurse();
        
        $product = App_DI_Definition_BankProduct::getInstance(BANK_KOTAK_SEMICLOSE_GPR);
        $productCode = $product->product->unicode;
        
        $productModel = new Products();
        $productDetailsArr = $productModel->getProductInfoByUnicode($productCode);
        $purseListOptions = $purseList->getPurseList($productDetailsArr->id);
        //**//
        
        
        $kotakModel = new Corp_Kotak_Customers();
        $productList = $kotakModel->corpProductList($filter_products = 'kotak_gpr');
        
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
                'maxlength' => '100',
                 'multioptions'    => array('' =>'Select Wallet'),      
            )
        );
        $purse->setRegisterInArrayValidator(false);
        $this->addElement($purse);
       
        $batch = new Zend_Form_Element_Select('batch_name');
        $batch->setOptions(
            array(
                'label'      => 'Batch Name ',
               
                'required'   => false,
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
        
        $btnp = new Zend_Form_Element_Hidden('purse');
        $btnp->setOptions(
            array(
            )
        );
        $this->addElement($btnp);
       
        
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
