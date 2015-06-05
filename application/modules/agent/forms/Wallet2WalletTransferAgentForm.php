<?php

class Wallet2WalletTransferAgentForm extends App_Operation_Form {

    public function init() {
	$this->_cancelLink = false;
 
	$prd = new Zend_Form_Element_Select('product_id');
	$prd->setOptions(array(
	    'label' => 'Product',
	    'required' => FALSE,
	    'filters' => array('StringTrim', 'StripTags'),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:215px;',
	    'maxlength' => '100',
 	    'multioptions' => array(),
	)); 
	$this->addElement($prd);
	
	$duration = $this->addElement('select', 'duration', array(
	    'filters' => array('StringTrim'),
	    'required' => false,
	    'label' => 'Duration: ',
	    'style' => 'width:215px;',
	    'multioptions' => Util::getDuration(),
	));

	$this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date', array(
	    'jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
	    'filters' => array('StringTrim'),
	    'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
	    'required' => false,
	    'label' => 'From: (e.g. dd-mm-yyyy) ',
	    'maxlength' => '20',
	    'style' => 'width:210px;'
	)));

	$this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date', array(
	    'jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
	    'filters' => array('StringTrim'),
	    'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
	    'required' => false,
	    'label' => 'To: (e.g. dd-mm-yyyy) ',
	    'maxlength' => '20',
	    'style' => 'width:210px;',
	)));

	$submit = new Zend_Form_Element_Submit('sub');
	$submit->setOptions(array(
	    'label' => 'Submit',
	    'required' => false,
	    'ignore' => true,
	    'title' => 'Submit',
	    'class' => 'tangerine',
	));
	$this->addElement($submit);

	$this->setElementDecorators(array(
	    'viewHelper',
	    'Errors',
	    array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'form-field-column edit')),
	    array('Label', array('tag' => 'dt', 'class' => 'form-name-column')),
	));
	$this->setDecorators(array(
	    'FormElements',
	    array(array('Value' => 'HtmlTag'), array('tag' => 'dl', 'class' => 'innerbox form')),
	    array('Description', array('placement' => 'prepend')),
	    'Form'
	));
    }

}

?>
