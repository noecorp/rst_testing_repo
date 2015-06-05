<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_SingleCardloadForm extends App_Corporate_Form
{
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
        $cardTypeOpt = Util::getCardType();
        $txnIdentifier = Util::getKptakTxnIdentifier();
        
        $durationArr = Util::getDuration();
        $purseList = new MasterPurse();
        
        $product = App_DI_Definition_BankProduct::getInstance(BANK_RATNAKAR_CORP);
        $productCode = $product->product->unicode;
        
        $productModel = new Products();
        $productDetailsArr = $productModel->getProductInfoByUnicode($productCode);
        
        
        $purseListOptions = $purseList->getPurseList($productDetailsArr->id);
        
          
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
            'maxlength' => 11
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
                'multioptions'    => array('' => 'Select','c' => 'Corporate','n' => 'Normal'),

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
        
        $this->addElement('text', 'wallet_code', array(
            'filters' => array('StringTrim'),
            'required' => TRUE,
            'label' => 'Wallet Code *',
            'style' => 'width:200px;',
        ));
        
         $value = $this->addElement('text', 'corporate_id', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Digits', array('StringLength', false, array(2, 15)),),
            //'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Corporate ID: *',
            'style'     => 'width:200px;',
            'maxlength' => 10
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
        $mode = $this->addElement('hidden', 'mode', array('value' =>'cr'        
        ));
        
        $currency = $this->addElement('hidden', 'currency', array('value' =>'365'        
        ));
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required'   => FALSE,
                'title'       => 'Yes',
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


