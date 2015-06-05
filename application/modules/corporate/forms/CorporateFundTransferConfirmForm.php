<?php
/**
 * Agent Fund Transfer Confirmation form
 *
 */

class CorporateFundTransferConfirmForm extends App_Corporate_Form
{
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
        
       /* $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'required' => true,
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        */

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Yes, Transfer Fund',
                'required'   => true,
                'class'     => 'tangerine',                
            )
        );
        $this->addElement($submit);
    }
}


