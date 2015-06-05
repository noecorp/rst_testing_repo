<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Boi_BankPendingForm extends App_Operation_Form
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
        
  
        
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_approval',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'maxlength'  => 10,
            'label'      => 'Approved Since (e.g. dd-mm-yyyy)',
            'style'      => 'width:200px;',)

        ));
        
        $this->addElement('text', 'appRefNo', array(
            'validators'    =>      array(),
            'required'      =>      false,
            'filters'       =>      array('StringToLower'),
            'label'         =>      'Application Ref No.',
            'maxlength'  => 10,
            'style'         =>      'width:200px;'
        ));
       
	$this->addElement('text', 'appRefNo', array(
            'validators'    =>      array(
                
                                    ),
            'required'      =>      false,
            'filters'       =>      array('StringToLower'),
            'label'         =>      'Application Ref No.',
            'style'         =>      'width:200px;'
        ));

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
