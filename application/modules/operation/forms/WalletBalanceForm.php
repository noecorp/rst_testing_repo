<?php

class WalletBalanceForm extends App_Operation_Form
{
  
    public function init()
    {   
        $this->_cancelLink = false;
        
        $bankList = new Banks();
        $bankListOptions = $bankList->getCustomerConceptBanks();
        
        $bankname = new Zend_Form_Element_Select('bank_unicode');
        $bankname->setOptions(
            array(
                'label'      => 'Bank Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
        
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Product *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select Product'),
            )
        );
        $product->setRegisterInArrayValidator(false);
        $this->addElement($product);   
        
        
        /*   $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => TRUE,
            'label'      => 'From: (e.g. dd-mm-yyyy) *',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  */
	
	$walletTypeLists = Util::walletType();
        $wallettype = new Zend_Form_Element_Select('wallettype');
        $wallettype->setOptions(array(
	    'label'=>'Wallet Type',
            'required'=>TRUE,
	    'filters'=> array('StringTrim','StripTags'),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:210px;',
	    'maxlength' => '100',
	    'multioptions'    => $walletTypeLists,         
            )
        );
        $this->addElement($wallettype);
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => TRUE,
            'label'      => 'As On Date: (e.g. dd-mm-yyyy) *',
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
        
        
        $submit = new Zend_Form_Element_Submit('btn_submit');
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
