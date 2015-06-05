<?php

class Corp_Kotak_CardLoadForm extends App_Corporate_Form
{
    public function  init()
    {   
        parent::init();
        $user = Zend_Auth::getInstance()->getIdentity();
        $cardTypeOpt = Util::getCardType();
        $txnIdentifier = Util::getKptakTxnIdentifier();
        
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
        
        
        $card_number = $this->addElement('text', 'identifier_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','alnum', array('StringLength', false, array(3, 16))),
            'required'   => false,
            'label'      => 'Identifier Number',
            'maxlength'  => '16',
        ));
        
        $txn_identifier_type = new Zend_Form_Element_Select('txn_identifier_type');
        $txn_identifier_type->setOptions(
            array(
                'label'      => 'Txn Identifier Type *',
                'multioptions'    => $txnIdentifier,

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
        $this->addElement($txn_identifier_type);
        
        $value = $this->addElement('text', 'amount', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(2, 8)),),
            //'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Amount: *',
            'style'     => 'width:200px;',
            'maxlength' => 10
        ));
        $this->addElement('text', 'txn_no', array(
            'filters' => array('StringTrim'),
            'required' => TRUE,
            'label' => 'Txn No. *',
            'style' => 'width:200px;',
        ));
        $cardType = new Zend_Form_Element_Select('card_type');
        $cardType->setOptions(
            array(
                'label'      => 'Card Type *',
                'multioptions'    => $cardTypeOpt,

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
        $this->addElement($cardType);
        
       
        
        $mode = new Zend_Form_Element_Select('mode');
        $mode->setOptions(
            array(
                'label'      => 'Txn Type *',
                'multioptions'    => array('cr'=>'Cr'),

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
        $this->addElement($mode);
        
        $currency = new Zend_Form_Element_Select('currency');
        $currency->setOptions(
            array(
                'label'      => 'Currency *',
                'multioptions'    => array('365'=>'Rs'),
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
        $this->addElement($currency);
        
        
        $this->addElement('text', 'wallet_code', array(
            'filters' => array('StringTrim'),
            'required' => TRUE,
            'label' => 'Wallet Code *',
            'style' => 'width:200px;',
        ));
        
        $remarks = new Zend_Form_Element_Textarea('narration');
        $remarks->setOptions(
            array(
                'label'      => 'Add your comment *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
               'style' => 'height:100px;width:300px;',
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 100)),
                                ),
            )
        );
        $this->addElement($remarks);
       
            
        $submit = new Zend_Form_Element_Submit('btn_add');
        $submit->setOptions(
            array(
                'label'      => 'Load Cardholder',
                'required'   => FALSE,
                'title'       => 'Load Cardholder',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
      
           
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    //array('Label',array('tag'=>'div')),
                   // array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'formrow')),
        ));
                // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div', 'class' => 'innerbox')),
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
     
    
    
    
}
?>
