<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentRemappingForm extends App_Operation_Form
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
        
        if(CURRENT_MODULE == MODULE_OPERATION) {
            $superAgent = new Zend_Form_Element_Select('super_agent');
            $superAgent->setOptions(array(
                    'label'     => 'New Super Agent *',
                    'style'     => 'width:auto;',
                    'required'   => true,
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array(
                                        'NotEmpty',
                                    ),
              ));
            $this->addElement($superAgent);
            
            $distAgent = new Zend_Form_Element_Select('distributor_agent');
            $distAgent->setOptions(array(
                    'label'     => 'New Distributor Agent *',
                    'multioptions' => array('' => 'Select'),
                    'required'   => true,
                    'style'     => 'width:auto;',
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array(
                                        'NotEmpty',
                                    ),
              ));
            $distAgent->setRegisterInArrayValidator(false);
            $this->addElement($distAgent);
        }

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Submit',
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