<?php
/*
 * Fund Transfer from Remitter to Beneficiary
 */
class Remit_Boi_FundTransferForm extends App_Agent_Form
{

     public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('POST');
        
        $amount = new Zend_Form_Element_Text('amount');
        $amount->setOptions(
            array(
                'label'      => 'Amount *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'Digits',array('StringLength', false, array(2, 6)),
                                ),
                 'maxlength' => '6',
            )
        );
        $this->addElement($amount); 
        
        $is_submit = $this->addElement('hidden', 'is_submit', array());
        
        $btn_auth_code = new Zend_Form_Element_Button('btn_auth_code');
        $btn_auth_code->setOptions(
            array(
                'label'      => 'Send Authorization Code',
                'required'   => false,
                'class'     => 'tangerine',
                
            )
        );
        $this->addElement($btn_auth_code);
        
        $code = new Zend_Form_Element_Text('auth_code');
        $code->setOptions(
            array(
                'label'      => 'Authorization Code *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'Digits',array('StringLength', false, array(6, 6)),
                                ),
                 'maxlength' => '6',
            )
        );
        $this->addElement($code);
        
        $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
          
        ));
        
        
        $senderMsg = new Zend_Form_Element_Text('sender_msg');
        $senderMsg->setOptions(
            array(
                'label'      => 'Add your message *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 180)),
                                ),
                 'maxlength' => '180',
                
            )
        );
        $this->addElement($senderMsg);
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Transfer Fund',
                'required'   => FALSE,
                'title'       => 'Transfer Fund',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    //array('Label',array('tag'=>'div')),
                   // array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'formrow')),
        ));
                // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div', 'class' => 'innerbox')),
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        

        
        
    }
     
    
    
    
}
?>
