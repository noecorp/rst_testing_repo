<?php

class CorporateFundingForm extends App_Agent_Form {

    public function init() {
        $this->_cancelLink=TRUE;
        $objFTType = new FundTransferType();
        $ftTypes = $objFTType->getFundTransferTypeForDropDown();


        $this->addElement('text', 'amount', array(
            'filters' => array('StringTrim'),
            'validators' => array('NotEmpty', 'Digits', array('StringLength', false, array(2, 8)),),
            'required' => true,
            'label' => 'Amount *',
            'style' => 'width:200px;',
            'maxlength' => 8,
        ));

        $this->addElement('select', 'fund_transfer_type_id', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Fund Transfer Type *',
            'style' => 'width:200px;',
            'multioptions' => $ftTypes,
            'onchange'=>'fundType()'
        ));


        $this->addElement('text', 'journal_no', array(
            'filters' => array('StringTrim'),
            'required' => FALSE,
            'label' => 'Journal No. *',
            'style' => 'width:200px;',
        ));

        $this->addElement('text', 'cheque_no', array(
            'filters' => array('StringTrim'),
            'required' => FALSE,
            'label' => 'Cheque No. *',
            'style' => 'width:200px;',
        ));

        $this->addElement('text', 'bank_of_cheque_issue', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Bank of cheque issue',
            'style' => 'width:200px;',
        ));

        $this->addElement('text', 'branch_of_cheque', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Branch of cheque',
            'style' => 'width:200px;',
        ));

        $this->addElement('text', 'other_txn', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Other Transaction No. *',
            'style' => 'width:200px;',
        ));

        $this->addElement('text', 'funding_details', array(
            'filters' => array('StringTrim'),
            'required' => false,
            'label' => 'Other Transaction Details',
            'style' => 'width:200px;',
        ));





        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_cheque_issue', array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters' => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required' => false,
            'label' => 'Date of issue',
            'maxlength' => '20',
            'style' => 'width:200px;',)
        ));


        $this->addElement('textarea', 'comments', array(
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 255)),),
            'required' => true,
            'label' => 'Comments *',
            //'disabled'   => 'disabled',
            'style' => 'width:400px;height:200px;',
        ));


        $submit = new Zend_Form_Element_Submit('btn_send');
        $submit->setOptions(
                array(
                    'label' => 'Fund Request',
                    'ignore' => true,
                    'required' => FALSE,
                    'title' => 'Fund Request',
                    'class' => 'tangerine',
                )
        );
        $this->addElement($submit);




        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'form-field-column edit')),
            array('Label', array('tag' => 'dt', 'class' => 'form-name-column')),
                //array('Label',array('tag'=>'div')),
                // array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'formrow')),
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
