<?php

class IfscSearchForm extends App_Operation_Form {

    public function init() {
	$this->_cancelLink = false;

	$bankList = new Banks();
	$bankListOptions = $bankList->getIFSCBanks();

	$bankname = new Zend_Form_Element_Select('bank_name');
	$bankname->setOptions(array(
	    'label' => 'Bank Name *',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:250px;',
	    'maxlength' => '100',
	    'multioptions' => $bankListOptions,
	));

	$ifsc = new Zend_Form_Element_Text('ifsc_code');
	$ifsc->setOptions(array(
	    'label' => 'IFSC Code',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '50',
	    'placeholder' => 'Ifsc Code'
	));

	$btn = new Zend_Form_Element_Hidden('sub');
	$btn->setOptions(array('value' => '1'));

	$submit = new Zend_Form_Element_Submit('submit');
	$submit->setOptions(array(
	    'label' => 'Submit',
	    'required' => false,
	    'ignore' => true,
	    'title' => 'Submit',
	    'class' => 'tangerine',
	));

	$this->addElements(array($bankname, $ifsc, $btn, $submit));
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
