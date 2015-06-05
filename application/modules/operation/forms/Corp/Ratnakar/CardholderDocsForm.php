<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Corp_Ratnakar_CardholderDocsForm extends App_Operation_Form
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
        
        $this->setAttrib('enctype', 'multipart/form-data');
        $Identification_type = new Zend_Form_Element_Select('id_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Identification Type *',
                'multioptions'    => Util::getRatIdentificationType($additional = TRUE),
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
        $this->addElement($Identification_type);
        
            
        $Identification_number = new Zend_Form_Element_Text('id_proof_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Identification Proof No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '16',
            )
        );
        $this->addElement($Identification_number);
       
        
       $doc_file = new Zend_Form_Element_File('id_doc_path');
       $doc_file->setLabel('Identification Proof (.jpg / .pdf) *')
	         ->setRequired(true)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
       

       $Identification_type = new Zend_Form_Element_Select('address_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Address Proof Type *',
                'multioptions'    => Util::getRatAddressProofType(),
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
        $this->addElement($Identification_type);
        
        
        $Identification_number = new Zend_Form_Element_Text('address_proof_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Address Proof No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                 'maxlength' => '16',
            )
        );
        $this->addElement($Identification_number);

     
        $doc_file = new Zend_Form_Element_File('address_doc_path');
        $doc_file->setLabel('Address Proof (.jpg / .pdf) *')
                  ->setRequired(true)
                  ->addValidator(new Zend_Validate_File_Size('5MB'));
        $this->addElement($doc_file);
        
      
        $remarks = new Zend_Form_Element_Textarea('comments');
        $remarks->setOptions(
            array(
                'label'      => 'Add your remarks *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'style' => 'height:100px;width:300px;',
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 255)),
                                ),
                 'maxlength' => '255',
                
            )
        );
        $this->addElement($remarks);
       
        
        $is_check = new Zend_Form_Element_Checkbox('is_check');
        $is_check->setCheckedValue(FLAG_YES);
        $is_check->setUncheckedValue(FLAG_NO);
        $is_check->setOptions(array(
              'label'      => 'Upgrade As KYC',
              'style'     => 'width:14px;'        
        ));
        $this->addElement($is_check);
       
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Upload Documents',
                'required'   => FALSE,
                'title'       => 'Upload Documents',
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