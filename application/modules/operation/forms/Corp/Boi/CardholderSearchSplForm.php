<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Boi_CardholderSearchSplForm extends App_Operation_Form
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
        
       $rctMasterModel = new RctMaster();
        $stateList = $rctMasterModel->getStateList();       
        
        $res_state = new Zend_Form_Element_Select('state');
        $res_state->setOptions(
            array(
                'label'      => 'State ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateList,                       
            )
        );
        $this->addElement($res_state);
       
        
       
     $name = new Zend_Form_Element_Text('pincode');
        $name->setOptions(
            array(
                'label'      => 'Pincode',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 100)),
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($name);
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_created',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'maxlength'  => 10,
            'label'      => 'Pending Since (e.g. dd-mm-yyyy)',
            'style'      => 'width:200px;',)

        ));
        
        $name = new Zend_Form_Element_Text('mobile');
        $name->setOptions(
            array(
                'label'      => 'Mobile',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(10, 10)),
                                ),
                'maxlength' => '10',
            )
        );
        $this->addElement($name);
        
        $name = new Zend_Form_Element_Text('ref_num');
        $name->setOptions(
            array(
                'label'      => 'Application Reference Number',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 20)),
                                ),
                'maxlength' => '20',
            )
        );
        $this->addElement($name);
        
        $btn = new Zend_Form_Element_Hidden('sub');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
        $this->addElement($btn);
        
        
        $btn = new Zend_Form_Element_Hidden('pin');
        $btn->setOptions(
            array()
        );
        $this->addElement($btn);
        
        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required'   => FALSE,
                'title'       => 'Submit',
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