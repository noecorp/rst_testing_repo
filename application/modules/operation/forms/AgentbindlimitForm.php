<?php
/**
 * Form for adding new master fee in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

//class AgentMasterfeeForm extends App_Operation_Form
class AgentbindlimitForm extends App_Operation_Form
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
        
        
        
        $agentlimit = new Agentlimit();
        $limitIdOptions = $agentlimit->getAgentlimits();
        
        $productId = new Zend_Form_Element_Select('agent_limit_id');
        $productId->setOptions(
            array(
                'label'      => 'Agent Limit *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'multiOptions' => $limitIdOptions,
            )
        );
        $this->addElement($productId);  
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_start',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            //'validators' =>  new Zend_Validate_Callback(array($new , 'currentDateValidation')),//array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'validators' =>  array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'label'      => 'Start Date: (e.g. dd-mm-yyyy) ',
            'style'     => 'width:200px;',)

        ));  
        
        
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
        $id = new Zend_Form_Element_Hidden('agent_id');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
       
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Assign Agent Limit',
                'required'   => FALSE,
                'title'       => 'Assign Agent Limit',
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