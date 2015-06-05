<?php

class Remit_Boi_AddBeneficiaryDetailsForm extends App_Agent_Form
{

     public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
        $excludeArr = array('BANK OF INDIA');
        $bankList = new BanksIFSC();
        $bankListOptions = $bankList->getBank($excludeArr);
        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Beneficiary Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '30',
            )
        );
        $this->addElement($name);
        $name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
        
        $nick_name = new Zend_Form_Element_Text('nick_name');
        $nick_name->setOptions(
            array(
                'label'      => 'Beneficiary Nick Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
            )
        );
        $this->addElement($nick_name);
        $nick_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
              
        
        $mobile = new Zend_Form_Element_Text('mobile');
        $mobile->setOptions(
            array(
                'label'      => 'Mobile no. *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(10, 10)),
                                ),
                 'maxlength' => '10',
                           
            )
        );
        $this->addElement($mobile);
        
         $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','EmailAddress',array('StringLength', false, array(9, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($email);
              
        
        
       $address_line1 = new Zend_Form_Element_Text('address_line1');
       $address_line1->setOptions(
            array(
                'label'      => 'Address Line 1 *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($address_line1);
        
         $address_line2 = new Zend_Form_Element_Text('address_line2');
         $address_line2->setOptions(
            array(
                'label'      => 'Address Line 2',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($address_line2);
        
        
        
        $bankname = new Zend_Form_Element_Select('bank_name');
        $bankname->setOptions(
            array(
                'label'      => 'Bank Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
        
        $ifsc_code = new Zend_Form_Element_Select('ifsc_code');
        $ifsc_code->setOptions(
            array(
                'label'      => 'IFSC Code *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select IFSC Code'),
                'style' => 'width:210px;',
            )
        );
        $ifsc_code->setRegisterInArrayValidator(false);
        $this->addElement($ifsc_code);      
        
        
        $accounttype = new Zend_Form_Element_Select('bank_account_type');
        $accounttype->setOptions(
            array(
                'label'      => 'Account Type *',
                'multioptions'    => Util::getBankAccountType(),
                            
                       
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
        $this->addElement($accounttype);
        
       
     
       $bank_account_number = new Zend_Form_Element_Text('bank_account_number');
        $bank_account_number->setOptions(
            array(
                'label'      => 'Bank Account No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Alnum',
                                ),
                'renderPassword'    => TRUE,                
                'maxlength' => '20',
            )
        );
        $this->addElement($bank_account_number);
        
        
        $conf_bank_account_number = new Zend_Form_Element_Text('conf_bank_account_number');
        $conf_bank_account_number->setOptions(
            array(
                'label'      => 'Confirm Bank Account No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Alnum',
                                ),
                'renderPassword'    => TRUE,
                 'maxlength' => '20',
            )
        );
        $this->addElement($conf_bank_account_number);
        
         $bank_location = new Zend_Form_Element_Text('branch_name');
        $bank_location->setOptions(
            array(
                'label'      => 'Branch Name *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($bank_location);
        
        
  
        $bank_area = new Zend_Form_Element_Text('branch_address');
        $bank_area->setOptions(
            array(
                'label'      => 'Branch Address',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '250',
            )
        );
        $this->addElement($bank_area);
        
        
       
        
        
        
        $bank_city = new Zend_Form_Element_Text('branch_city');
        $bank_city->setOptions(
            array(
                'label'      => 'Branch City ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($bank_city);
        
        $btn_auth_code = new Zend_Form_Element_Button('btn_auth_code');
        $btn_auth_code->setOptions(
            array(
                'label'      => 'Send Beneficiary Authorization Code',
                'required'   => false,
                'class'     => 'tangerine',
                
            )
        );
        $this->addElement($btn_auth_code);
        
        $code = new Zend_Form_Element_Text('auth_code');
        $code->setOptions(
            array(
                'label'      => 'Authorization Code *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'Digits',array('StringLength', false, array(6, 6)),
                                ),
                 'maxlength' => '6',
            )
        );
        $this->addElement($code);       
        
       $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
          
        ));
       
       $ifsc = $this->addElement('hidden', 'ifsc', array(
          
        ));
        
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Save Beneficiary',
                'required'   => FALSE,
                'title'       => 'Save Beneficiary',
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
?>
