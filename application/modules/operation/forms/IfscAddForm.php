<?php

class IfscAddForm extends App_Operation_Form {

    public function init() {
	$this->_cancelLink = TRUE;

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

	$micr = new Zend_Form_Element_Text('micr_code');
	$micr->setOptions(array(
	    'label' => 'Micr Code',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '50',
	    'placeholder' => 'Micr Code'
	));

	$branch_name = new Zend_Form_Element_Textarea('branch_name');
	$branch_name->setOptions(array(
	    'label' => 'Branch Name',
	    'placeholder' => 'Branch Name',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '250',
	    'rows' => "3"
	));
	$address = new Zend_Form_Element_Textarea('address');
	$address->setOptions(array(
	    'label' => 'Address',
	    'placeholder' => 'Address',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '250',
	    'rows' => "5"
	));
	$contact = new Zend_Form_Element_Text('contact');
	$contact->setOptions(array(
	    'label' => 'Contact',
	    'placeholder' => 'Contact',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '250',
	));
	$city = new Zend_Form_Element_Text('city');
	$city->setOptions(array(
	    'label' => 'City',
	    'placeholder' => 'City',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '250',
	    'rows' => "4"
	));
	$district = new Zend_Form_Element_Text('district');
	$district->setOptions(array(
	    'label' => 'District',
	    'placeholder' => 'District',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '250',
	    'rows' => "4"
	));
	$state = new Zend_Form_Element_Text('state');
	$state->setOptions(array(
	    'label' => 'State',
	    'placeholder' => 'State',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:245px;',
	    'maxlength' => '250',
	    'rows' => "4"
	));
	$enable_for = new Zend_Form_Element_Select('enable_for');
	$enable_for->setOptions(array(
	    'label' => 'Enable for',
	    'required' => TRUE,
	    'filters' => array('StringTrim', 'StripTags',),
	    'validators' => array('NotEmpty'),
	    'style' => 'width:250px;',
	    'maxlength' => '100',
	    'multioptions' => array(
		'', 
		strtolower(TXN_IMPS) => TXN_IMPS, 
		strtolower(TXN_NEFT) => TXN_NEFT, 
		ENABLE_FOR_ALL => 'Both'),
	)); 
	
	$submit = new Zend_Form_Element_Submit('submit');
	$submit->setOptions(array(
	    'label' => 'Submit',
	    'required' => false,
	    'ignore' => true,
	    'title' => 'Submit',
	    'class' => 'tangerine',
	));

	$this->addElements(array($bankname, $ifsc, $micr, $branch_name, $address, $contact, $city, $district, $state, $enable_for, $submit));
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
