<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentdocsForm extends App_Operation_Form
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
        //set Attribute type
        
        $this->setAttrib('enctype', 'multipart/form-data');
        
       
        
        $doc_type = new Zend_Form_Element_Select('doc_type[]');
        $doc_type->setOptions(
            array(
                'label'      => 'Document Type *',
                'multioptions'    => Util::getDoctype(),
                            
                       
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
        $doc_type->setRegisterInArrayValidator(false);
        $this->addElement($doc_type);
        
       $doc_file = new Zend_Form_Element_File('doc_path[]');
       $doc_file->setLabel('Document File Path')
	         ->setRequired(true)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
         
        $limit = new Zend_Form_Element_Hidden('limit');
        $limit->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($limit);
                 
           
           
       // creating object for submit button
		 $button = new Zend_Form_Element_Button('button');
		 $button->setLabel('upload more')
				 ->setAttrib('id','button')
				 ->setAttrib('class','tangerine');

		// adding elements to form Object
		$this->addElement( $button);
                 
         
        $submit = new Zend_Form_Element_Submit('submitbutton');
        $submit->setOptions(
            array(
                'label'      => 'Upload File',
                'required'   => FALSE,
                'title'       => 'Upload File',
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


