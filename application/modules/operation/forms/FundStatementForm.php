<?php

class FundStatementForm extends App_Operation_Form {

    public function init() {
        $this->_cancelLink = false;

        $bankStatement = new BankStatement();

        $bankStatements = $bankStatement->getUnsettledBankStatementByAmount($this->getAttrib('amount'));

        $bankStatementArr = array();

        foreach ($bankStatements as $bankStatement) {
            $bankStatementArr[$bankStatement->id] = $bankStatement->amount.'->Journal_or_cheque_no'.$bankStatement->journal_or_cheque_no;
        }



        $this->addElement('radio', 'bank_statement', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'label' => 'Statement Amount *',
            'style' => 'width:200px;margin-right:20px',
            'multioptions' => $bankStatementArr,
        ));


        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
                array(
                    'label' => 'Statement',
                    'required' => false,
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
