<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class CustomeraddcityForm extends App_Operation_Form
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
        
        $statelist = new RctMaster();
        $stateOptionsList = $statelist->getStateList();
        

        $res_state = new Zend_Form_Element_Select('state_code');
        $res_state->setOptions(
            array(
                'label'      => 'State *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateOptionsList,                       
            )
        );
        $this->addElement($res_state);
             
        $res_address2 = new Zend_Form_Element_Text('ref_code');
        $res_address2->setOptions(
            array(
                'label'      => 'City Code *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 15)),
                                ),
                'maxlength' => '5',
            )
        );
        $this->addElement($res_address2);
        $res_address2->addValidator('Alpha', true, array('allowWhiteSpace' => false));
        
        $res_address1 = new Zend_Form_Element_Text('ref_desc');
        $res_address1->setOptions(
            array(
                'label'      => 'City Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 25)),
                                ),
                 'maxlength' => '25',
            )
        );
        $this->addElement($res_address1);
        $res_address1->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
        
        
        $res_address = new Zend_Form_Element_Text('zone_name');
        $res_address->setOptions(
            array(
                'label'      => 'District',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 25)),
                                ),
                 'maxlength' => '25',
            )
        );
        $this->addElement($res_address);
        $res_address->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
      
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save City Details',
                'required'   => FALSE,
                'title'       => 'Save City Details',
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