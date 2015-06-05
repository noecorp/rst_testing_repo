<?php
/**
 * User imoprt crn form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Mvc_Axis_ImportCRNAdddetailsForm extends App_Operation_Form
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
        $objBanks = new Banks();
        $bankOptions = $objBanks->getMVCProductBanks();
            
        // set the form's method
        //$this->setMethod('post');
        
        /*$bank_id = $this->addElement('select', 'bank_id', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 5)),),
            'required'   => true,
            'label'      => 'Bank Name *',
            'multiOptions' => $bankOptions,
        ));*/
        
        
        
        
        $bank_name = $this->addElement('text', 'bank_name', array(

            //'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(1, 5)),),
            //'required'   => false,
            'label'      => 'Bank Name',
            'readonly' => 'readonly'
            //'multiOptions' => $bankOptions,
        ));
        
        
        $product_id = $this->addElement('select', 'product_id', array(

            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 5)),),
            'required'   => false,
            'label'      => 'Product Name',
            //'multiOptions' => $bankOptions,
        ));
        
       $crn_file = new Zend_Form_Element_File('crn_file');
       $crn_file->setLabel('Specify CRN File *')
	         ->setRequired(true)
                 ->addValidator(new Zend_Validate_File_Size('10MB'));
       $this->addElement($crn_file);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Next',
                'required'   => FALSE,
                'title'       => 'Next',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        
        $bank_id = $this->addElement('hidden', 'bank_id', array(

            //'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(1, 5)),),
            //'required'   => true,
            //'label'      => 'Bank Name *',
        ));
        
        
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