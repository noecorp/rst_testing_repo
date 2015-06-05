<?php
/**
 * Default delete form, it's used to prevent CSRF attacks
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Kotak_UploadcrnForm extends App_Operation_Form
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
  
        $kotakModel = new Corp_Kotak_Customers();
        $productList = $kotakModel->corpProductList($filter_products = 'kotak_gpr');
        
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Select Product *',
                'multioptions'    => $productList,
                            
                       
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
        
       $doc_file = new Zend_Form_Element_File('doc_path');
       $doc_file->setLabel('CRN File (Max Upload Size 5MB) *')
	         ->setRequired(true)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
         
        
                 
  
         
        $submit = new Zend_Form_Element_Submit('submitbutton');
        $submit->setOptions(
            array(
                'label'      => 'Upload CRN',
                'required'   => FALSE,
                'title'       => 'Upload CRN',
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


