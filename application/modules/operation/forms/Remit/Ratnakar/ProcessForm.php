<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Remit_Ratnakar_ProcessForm extends App_Operation_Form
{
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
       /**
     * This form does not have a cancel link
     * 
     * @var mixed
     * @access protected
     */
     
    public function init() {
        // init the parent
        parent::init();
        
    
        // set the form's method
        $this->setMethod('post');
        
        $rem_req_id = new Zend_Form_Element_Hidden('remit_request_id');
        $rem_req_id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($rem_req_id);
        
        
        $cr_response = new Zend_Form_Element_Hidden('cr_response');
        $cr_response->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
//                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($cr_response);
        
        $final_response = new Zend_Form_Element_Hidden('final_response');
        $final_response->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
//                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($final_response);
        
        
        $status = new Zend_Form_Element_Hidden('status');
        $status->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
//                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($status);
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Confirm',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Confirm',
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
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
}


