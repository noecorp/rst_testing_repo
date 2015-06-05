<?php

//class RptAgentFundRequestsForm extends Zend_Form
class UploadKotakBankStatementForm extends App_Operation_Form {

    public function init() {
        
        $banksOptionsArr = Util::getStamentBankList();
        $bank = new Zend_Form_Element_Select('bank_id');
        $bank->setOptions(
            array(
                'label'      => 'Bank Name *',
                'multioptions'    => $banksOptionsArr,

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
        $this->addElement($bank);
        
        $this->addElement('file', 'upload', array(
            'label' => 'Upload Bank Statement',
            'ignore' => true,
            'required'   =>FALSE
        ));

       $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
                array(
                    'label' => 'Upload',
                    'ignore' => true,
                    'title' => 'Submit',
                    'class' => 'tangerine'
                )
        );

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
