<?php

//class RptAgentFundRequestsForm extends Zend_Form
class UploadBankStatementForm extends App_Operation_Form {

    public function init() {
        
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

?>
