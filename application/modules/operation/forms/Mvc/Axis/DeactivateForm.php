<?php
/**
 * Deactivate / Block Cardholder Form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Mvc_Axis_DeactivateForm extends App_Operation_Form
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
        $remarks = new Zend_Form_Element_Textarea('remarks');
        $remarks->setOptions(
            array(
                'label'      => 'Add your remarks *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 255)),
                                ),
               'style' => 'height:100px;width:300px;',
               'maxlength' => '255',
            )
        );
        $this->addElement($remarks);
        
        
      
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Yes, Block Cardholder',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Yes, Block Cardholder',
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


