<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_ChangeStatusForm extends App_Corporate_Form
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
        $product = new Zend_Form_Element_Select('purse_id');
        $product->setOptions(
            array(
                'label'      => 'Purse Name *',
                'multioptions'    => array('' => 'Select Product'),

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($product);
        
         
        $this->setAttrib('enctype', 'multipart/form-data');
        
//       $doc_file = new Zend_Form_Element_File('doc_path');
//       $doc_file->setLabel('Document File Path')
//	         ->setRequired(true)
//                 ->addValidator(new Zend_Validate_File_Size('5MB'));
//       $this->addElement($doc_file);
//         
//        
//                 
//  
//         
        $submit = new Zend_Form_Element_Submit('submitbutton');
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


