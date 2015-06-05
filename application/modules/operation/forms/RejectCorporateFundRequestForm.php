
<?php

class RejectCorporateFundRequestForm extends App_Operation_Form {

    public function init() {
      
        $params=$this->getAttrib('params');
        $this->_cancelLink=$params['cancelLink'];
        $this->addElement('hidden', 'corporate_funding_id', array(
            'filters' => array('StringTrim'),
            'required' => FALSE,
            'value'=>  $params['corporate_funding_id']    
        ));
        
        
        $this->addElement('textarea', 'settlement_remarks', array(
            'filters' => array('StringTrim'),
            'required' => FALSE,
            'label'=>'Reject Remarks:',
            'rows'=>8,
            'cols'=>70
        ));
        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
                array(
                    'label' => 'Yes, Reject Fund Request',
                    'required' => false,
                    'ignore' => true,
                    'title' => 'Yes, Reject',
                    'class' => 'tangerine'
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

?>
