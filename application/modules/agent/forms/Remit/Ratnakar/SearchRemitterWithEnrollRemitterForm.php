<?php
/**
 * Search Remitter Form
 *
 */

class Remit_Ratnakar_SearchRemitterWithEnrollRemitterForm extends App_Agent_Form
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
        
        $phone = new Zend_Form_Element_Text('mobile');
        $phone->setOptions(
            array(
                'label'      => 'Enter Remitter Mobile number *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits',array('StringLength', false, array(10, 10)),
                                ),
                'maxlength' => '10',
                'autocomplete'=> 'off',
            )
        );
        $this->addElement($phone);
        
        
         $btn_auth_code = $this->addElement('button', 'search_remitter', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Search Remitter',
           // 'onclick'     => "javascript:search_remitter();",
            'class'     => 'tangerine',
            
        ));
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