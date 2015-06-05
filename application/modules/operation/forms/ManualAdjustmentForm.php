<?php

/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */
class ManualAdjustmentForm extends App_Operation_Form {

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

	//get bank unicode
	$bankPaytronic = App_DI_Definition_Bank::getInstance(BANK_RATNAKAR);
	$productPatUnicode = $bankPaytronic->bank->unicode;

	//get bank id using bank unicode
	$objBankk = new Banks();
	$bankInfo = $objBankk->getBankbyUnicode($productPatUnicode);
	$rat_bank_id = $bankInfo['id'];

	$productModel = new Products();
	$prodArr = $productModel->getListProductsByprogram($productPatUnicode, array(PROGRAM_TYPE_CORP,PROGRAM_TYPE_DIGIWALLET));
	$productOptionsArr =array();
	foreach ($prodArr as $val) { 
	    $productOptionsArr[$val['id']] = $val['name']; 
        }
	$product = new Zend_Form_Element_Select('product_id');
	$product->setOptions(array(
	    'label' => 'Product Name *',
	    'multioptions' => $productOptionsArr,
	    'required' => true,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty',),
	));
	$this->addElement($product);
	$this->setAttrib('enctype', 'multipart/form-data');

	$doc_file = new Zend_Form_Element_File('doc_path');
	$doc_file->setLabel('CRN File (Max Upload Size 5MB)')
		->setRequired(true)
		->addValidator(new Zend_Validate_File_Size('5MB'));
	$this->addElement($doc_file);

	$callecs = new Zend_Form_Element_Radio('callecs');
	$callecs->setOptions(array(
	    'label' => 'Call to ECS *',
	    'multiOptions' => array(FLAG_Y => 'yes', FLAG_N => 'No'),
	    'value' => FLAG_N,
	    'required' => true,
	    'validators' => array('NotEmpty'),
	    'style' => 'margin-bottom: 10px;margin-right: 10px; width: auto;'
	));
	$this->addElement($callecs);

	$submit = new Zend_Form_Element_Submit('submitbutton');
	$submit->setOptions(
		array(
		    'label' => 'Upload Manual Adjustment',
		    'required' => FALSE,
		    'title' => 'Upload Manual Adjustment',
		    'class' => 'tangerine',
		)
	);
	$this->addElement($submit);


	$this->setElementDecorators(array(
	    'viewHelper',
	    'Errors',
	    array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'form-field-column edit')),
	    array('Label', array('tag' => 'dt', 'class' => 'form-name-column')),
	));
	// We want to display a 'failed authentication' message if necessary;
	// we'll do that with the form 'description', so we need to add that
	// decorator.
	$this->setDecorators(array(
	    'FormElements',
	    //array('HtmlTag', array('tag' => 'div', 'class' => 'innerbox')),
	    array(array('Value' => 'HtmlTag'), array('tag' => 'dl', 'class' => 'innerbox form')),
	    array('Description', array('placement' => 'prepend')),
	    'Form'
	));
    }

}
