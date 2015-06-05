<?php

class AgentVirtualFundingForm extends App_Agent_Form {

    public function init() {
        $this->_cancelLink = TRUE;
        $objFTType = new FundTransferType();
        $ftTypes = $objFTType->getFundTransferTypeForDropDown();
 
        $this->addElement('text', 'amount', array(
            'filters' => array('StringTrim'),
            'validators' => array('NotEmpty', 'Digits', array('StringLength', false, array(1, 8)),),
            'required' => true,
            'label' => 'Amount *',
            'style' => 'width:200px;',
            'maxlength' => 8,
        ));

        $this->addElement('text', 'utr', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'UTR No. *',
            'style' => 'width:200px;',
        ));

        $this->addElement('textarea', 'comments', array(
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 255)),),
            'required' => true,
            'label' => 'Comments *',
            'style' => 'width:400px;height:200px;',
        ));


        $submit = new Zend_Form_Element_Submit('btn_send');
        $submit->setOptions(array(
            'label' => 'Fund Request',
            'ignore' => true,
            'required' => FALSE,
            'title' => 'Fund Request',
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
