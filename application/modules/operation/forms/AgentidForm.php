<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentidForm extends App_Operation_Form
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
        /*$date_of_birth = new Zend_Form_Element_Text('date_of_birth');
        $date_of_birth->setOptions(
            array(
                'label'      => 'Date Of Birth *',
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
        $this->addElement($date_of_birth);
        */
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_of_birth',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'maxlength'  => 10,
            'label'      => 'Date of Birth: (e.g. dd-mm-yyyy) *',
            'style'     => 'width:200px;',)

        ));
        
       
        
        $gender = new Zend_Form_Element_Select('gender');
        $gender->setOptions(
            array(
                'label'      => 'Gender *',
                'multioptions'    => Util::getGender(),
                            
                       
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
        $this->addElement($gender);
        
      
        
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
            )
        );
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
                'maxlength' => '16',
            )
        );
        $this->addElement($Identification_number);
       
        
       
       $doc_file = new Zend_Form_Element_File('id_doc_path');
       $doc_file->setLabel('Identification Document File Path')
	         ->setRequired(false)
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
       $doc_file->setLabel('Address Document File Path')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($doc_file);
        
       
           $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('passport_expiry',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Passport Expiry: (e.g. dd-mm-yyyy) ',
            'style'     => 'width:200px;',)

        ));
           
        $pan_number_status = new Zend_Form_Element_Select('pan_number_status');
        $pan_number_status->setOptions(
            array(
                'label'      => 'PAN Status *',
                'multioptions'    => Util::getPanCardOptions(),
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
        $this->addElement($pan_number_status);
        
        
        $pan_number = new Zend_Form_Element_Text('pan_number');
        $pan_number->setOptions(
            array(
                'label'      => 'PAN *',
                'required'   => false,
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
        $this->addElement($pan_number);
        
     
       
        
        $agent_detail_id = new Zend_Form_Element_Hidden('agent_detail_id');
        $agent_detail_id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
         $this->addElement($agent_detail_id);
        
       
        
         
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Identification Details',
                'required'   => FALSE,
                'title'       => 'Save Identification Details',
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