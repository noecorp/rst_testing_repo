<?php
/**
 * Search Remitter Form
 *
 * @category operation
 * @package operation_forms
 * @copyright Transerv
 */

class Corp_Kotak_SearchCardholderForm extends App_Agent_Form
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
        
        $phone = new Zend_Form_Element_Text('medi_assist_id');
        $phone->setOptions(
            array(
                'label'      => 'Medi Assist ID *',
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
        
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Search Cardholder',
                'required'   => FALSE,
                'title'       => 'Search Cardholder',
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