<?php
/**
 * Search Remitter Form
 *
 * @category operation
 * @package operation_forms
 * @copyright Transerv
 */

class Corp_Ratnakar_LoadCardholderForm extends App_Agent_Form
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
        $hospital = new Corp_Ratnakar_Hospital();
        $hospitalArr = $hospital->getHospital();
        $phone = new Zend_Form_Element_Select('hospital_id');
        $phone->setOptions(
            array(
                'label'      => 'Hospital *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Alnum',array('StringLength', false, array(1, 10)),
                                ),
              'multioptions'    => $hospitalArr,
                )
        );
        $this->addElement($phone);
        
        $phone = new Zend_Form_Element_Text('amount');
        $phone->setOptions(
            array(
                'label'      => 'Amount *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Alnum',array('StringLength', false, array(1, 10)),
                                ),
//                'maxlength' => '10',
            )
        );
        $this->addElement($phone);
        
         $btn_auth_code = $this->addElement('submit', 'btn_auth_code', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Send Authorization Code',
            //'onclick'     => "javascript:sendAuthCode();",
            'class'     => 'tangerine',
        )); 
        
          $auth_code = $this->addElement('text', 'auth_code', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(4, 10)),),
            'required'   => true,
            'label'      => 'Authorization Code *',
            'style'     => 'width:200px;',
            'maxlength'  => '6',
        ));
          
        $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
          
        ));
          $is_submit = $this->addElement('hidden', 'is_submit', array());
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Load Cardholder',
                'required'   => FALSE,
                'title'       => 'Load Cardholder',
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