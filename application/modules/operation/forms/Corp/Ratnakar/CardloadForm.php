<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_CardloadForm extends App_Operation_Form
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
        
        
        $this->setAttrib('enctype', 'multipart/form-data');
        
       $doc_file = new Zend_Form_Element_File('doc_path');
       $doc_file->setLabel('Document File Path *')
	         ->setRequired(true)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
         
       $cnt = new Zend_Form_Element_Text('count');
       $cnt->setOptions(
            array(
                'label'      => 'Count of Loads *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits',
                                ),
                'maxlength' => '3',
            )
        );
        $this->addElement($cnt);
        
       $cnt = new Zend_Form_Element_Text('value');
       $cnt->setOptions(
            array(
                'label'      => 'Value of Loads *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits',
                                ),
                'maxlength' => '6',
            )
        );
        $this->addElement($cnt); 
                 
  
         
        $submit = new Zend_Form_Element_Submit('submitbutton');
        $submit->setOptions(
            array(
                'label'      => 'Upload Wallet',
                'required'   => FALSE,
                'title'       => 'Upload Wallet',
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


