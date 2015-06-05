<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class ForgotPasswordForm extends App_Bank_Form
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
        
        $username = new Zend_Form_Element_Text('email');
        $username->setOptions(
            array(
                'label'      => 'Email *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','EmailAddress', array('StringLength', false, array(8, 50))
                                ),
                
            )
        );
        $this->addElement($username);
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            'required'   => true,
            'label'      => 'Date of Birth *',
            'style'     => 'width:200px;',
            'maxlength'  => '10',
        )));
        
         $Identification_type = new Zend_Form_Element_Select('Identification_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Identification Type *',

                'multioptions'    => Util::getIdentificationType(),
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'style'     => 'width:210px;',                
            )
        );
        //$Identification_type->addDecorator('Identification Type<br /> (as per our records) *',array('escape'=>false));
        //$Identification_type->getDecorator('label')->setOption('escape',false);
        //$Identification_type->setLabel('Identification Type <br />(as per our records) *');
        $this->addElement($Identification_type);
        
       
        
        
        $Identification_number = new Zend_Form_Element_Text('Identification_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Identification No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
            )
        );
        $this->addElement($Identification_number);
       
        
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
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        
      
    }
}