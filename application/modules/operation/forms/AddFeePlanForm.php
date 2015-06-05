<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AddFeePlanForm extends App_Operation_Form
{
    /**
     * This form does not have a cancel link
     * 
     * @var mixed
     * @access protected
     */
   // protected $_cancelLink = false;
    
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
        
        /*$agentgroup = new Agentgroup();
        $agentgroupOptions = $agentgroup->getGroups();
        */
            
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Fee Plan Name *',
                
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 100)),
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($name);
        
         $description = $this->addElement('textarea', 'description', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 255)),),
            'required'   => true,
            'label'      => 'Description *',
            //'disabled'   => 'disabled',
            'style'     => 'width:400px;height:200px;',            
        ));
            
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Fee Plan',
                'required'   => TRUE,
                'title'       => 'Save Fee Plan',
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
      
        $this->setDecorators(array(
            'FormElements',
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
}