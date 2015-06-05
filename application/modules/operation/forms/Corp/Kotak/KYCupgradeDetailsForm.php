<?php
/*
 * Add Remitter Form
 */
class Corp_Kotak_KYCupgradeDetailsForm extends App_Operation_Form
{
  
    public function  init()
    {       
         $this->setAttrib('enctype', 'multipart/form-data');
         
        
        
        
        $Identification_type = new Zend_Form_Element_Select('id_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Identification Type *',
                'multioptions'    => Util::getIdentificationType($additional = TRUE),
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
       $doc_file->setLabel('Identification Document *')
	         ->setRequired(true)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
       
        

       $Identification_type = new Zend_Form_Element_Select('address_proof_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Address Proof Type *',
                'multioptions'    => Util::getAddressProofType(),
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
       $doc_file->setLabel('Address Document *')
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
        
                
        $submit = new Zend_Form_Element_Submit('btn_add');
        $submit->setOptions(
            array(
                'label'      => 'Update Cardholder',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Update Cardholder',
                //'onclick'    => 'Javascript:checkDOB();',
            )
        );
        $this->addElement($submit);
         
      
         
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        
        
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
?>
