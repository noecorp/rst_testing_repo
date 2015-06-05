<?php
/**
 * Search Remitter Form
 *
 * @category operation
 * @package operation_forms
 * @copyright Transerv
 */

class Remit_Boi_RefundRemitterForm extends App_Agent_Form
{
    /**
     * This form does not have a cancel link
     * 
     * @var mixed
     * @access protected
     */
    protected $_cancelLink = false;
    
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
        
        $btn_auth_code = new Zend_Form_Element_Button('btn_auth_code');
        $btn_auth_code->setOptions(
            array(
                'label'      => 'Resend Authorization Code',
                'required'   => false,
                'class'     => 'tangerine',
                
            )
        );
        $this->addElement($btn_auth_code);
     
        $is_submit = $this->addElement('hidden', 'is_submit', array());
        
//        $flgSess = new Zend_Form_Element_Hidden('flgSess');
//        $flgSess->setOptions(
//            array(
//               'value' => 1
//            )
//        );
//       
//        $this->addElement($flgSess);
        
        $submit = new Zend_Form_Element_Submit('btn_refund');
        $submit->setOptions(
            array(
                'label'      => 'Refund',
                'required'   => FALSE,
                'title'       => 'Refund',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    
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