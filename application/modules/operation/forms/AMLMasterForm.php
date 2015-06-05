<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AMLMasterForm extends App_Operation_Form
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
  
        $this->setAttrib('enctype', 'multipart/form-data');
        
//        $xml_source = new Zend_Form_Element_Select('xml_source');
//        $xml_source->setOptions(
//            array(
//                    'label'      => 'Source',
//                    'required'   => true,
//                    'filters'    => array(
//                                        'StringTrim',
//                                        'StripTags',
//                                    ),
//                    'validators' => array(
//                                       'NotEmpty',
//                                    ),
//                    'multioptions'    => Array(''=>'Select','UN'=>'United Nations'),         
//            )
//        );
//        $this->addElement($xml_source);
//        	
//       	$url = new Zend_Form_Element_Text('url');
//				$url->setOptions(
//				    array(
//				        'label'      => 'Enter the URL',
//				        'filters'    => array(
//				                            'StringTrim',
//				                            'StripTags',
//				                        ),
//				        'validators' => array(
//				                            array(
//				                                'Callback',
//				                                true,
//				                                array(
//				                                    'callback' => function($value) {
//				                                        return Zend_Uri::check($value);
//				                                    }
//				                                ),
//				                                'messages' => array(
//				                                    Zend_Validate_Callback::INVALID_VALUE => 'Please enter a valid URL',
//				                                ),
//				                            ),
//				                        ),
//				    )
//				);
//				
//       $this->addElement($url);
       
       $doc_file = new Zend_Form_Element_File('doc_path');
       $doc_file->setLabel('AML XML File (Max Upload Size 5MB) *')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
         
        
                 
  
         
        $submit = new Zend_Form_Element_Submit('submitbutton');
        $submit->setOptions(
            array(
                'label'      => 'Upload AML',
                'required'   => FALSE,
                'title'       => 'Upload AML',
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


