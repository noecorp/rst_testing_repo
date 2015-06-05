<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_ApproveForm extends App_Operation_Form
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
        $this->_cancelLink = false;
        
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
                'style' => 'height:100px;width:300px;',
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 255)),
                                ),
                 'maxlength' => '255',
                
            )
        );
        $this->addElement($remarks);
        
        //if(isset($info['parent_agent_id']) && $info['parent_agent_id'] == '0') {
                               
        //$superAgent = new Zend_Form_Element_Checkbox('is_super_agent');
        //$superAgent->setCheckedValue(SUPER_AGENT_DB_VALUE);
        //$superAgent->setUncheckedValue('0');
        //$superAgent->setOptions(array(
        //      'label'      => 'Super Agent',
        //      'style'     => 'width:14px;'        
        //  ));
        //$this->addElement($superAgent);

        //}
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Yes, Approve',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Yes, Approve',
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


