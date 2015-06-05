<?php
/*
 * Add Bene Form
 */
class Remit_Kotak_AddBeneficiaryWithRemitterDetailsForm extends App_Agent_Form
{

     public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
//        $excludeArr = array('BANK OF INDIA');
        $excludeArr = array();
        $bankList = new BanksIFSC();
        $bankListOptions = $bankList->getBank($excludeArr);
        
        $name = new Zend_Form_Element_Text('beni_name');
        $name->setOptions(
            array(
                'label'      => 'Beneficiary Name *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '30',
                  'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($name);
        $name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
        
        $nick_name = new Zend_Form_Element_Text('beni_nick_name');
        $nick_name->setOptions(
            array(
                'label'      => 'Beneficiary Nick Name *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
                  'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($nick_name);
        $nick_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
              
        
        $mobile = new Zend_Form_Element_Text('beni_mobile');
        $mobile->setOptions(
            array(
                'label'      => 'Mobile no. *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(10, 10)),
                                ),
                 'maxlength' => '10',
                'autocomplete'=> 'off',
                'style'     => 'background:#FFFF99',
                           
            )
        );
        $this->addElement($mobile);
        
        
              
        
        
       $address_line1 = new Zend_Form_Element_Text('beni_address_line1');
       $address_line1->setOptions(
            array(
                'label'      => 'Address Line 1 *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '30',
                'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($address_line1);
        
         $address_line2 = new Zend_Form_Element_Text('beni_address_line2');
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
     
        $ifsc_code = new Zend_Form_Element_Text('beni_ifsc_code');
        $ifsc_code->setOptions(
            array(
                'label'      => 'IFSC Code *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '100',
                 'style' => 'width:210px;background:#FFFF99',
            )
        );
        $this->addElement($ifsc_code);
        
        $bankname = new Zend_Form_Element_Select('beni_bank_name');
        $bankname->setOptions(
            array(
                'label'      => 'Bank Name *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'maxlength' => '100',
                'style' => 'width:210px;background:#FFFF99',
                'multioptions'    => $bankListOptions,
            )
        );
        $this->addElement($bankname);
        $bankstate = new Zend_Form_Element_Select('beni_bank_state');
        $bankstate->setOptions(
            array(
                    'label'      => 'Branch State *',
                    'required'   => false,
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array(
                                       'NotEmpty',
                                    ),
                    'style' => 'width:210px;background:#FFFF99',
                    'maxlength' => '100',
                    'multioptions'    => array('' =>'Select State'),
                    
            )
        );
        $bankstate->setRegisterInArrayValidator(false);
        $this->addElement($bankstate);    
        
        
        
        $branchcity = new Zend_Form_Element_Select('beni_branch_city');
        $branchcity->setOptions(
            array(
                    'label'      => 'Branch City *',
                    'required'   => false,
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array(
                                       'NotEmpty',
                                    ),
                    'style' => 'width:210px;background:#FFFF99',
                    'maxlength' => '100',
                    'multioptions'    => array('' =>'Select City')
                    
            )
        );
        $branchcity->setRegisterInArrayValidator(false);
        $this->addElement($branchcity);
       
        $branch_name = new Zend_Form_Element_Select('beni_branch_name');
        $branch_name->setOptions(
            array(
                'label'      => 'Branch Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '100',
                'style'     => 'background:#FFFF99',
//                'multioptions'    => array('' =>'Select Branch'),
            )
        );
        $branch_name->setRegisterInArrayValidator(false);
        $this->addElement($branch_name);
        
        
  
        $bank_area = new Zend_Form_Element_Text('beni_branch_address');
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
        
        
       
        
        
        
       
        
         $accounttype = new Zend_Form_Element_Select('beni_bank_account_type');
        $accounttype->setOptions(
            array(
                'label'      => 'Account Type *',
                'multioptions'    => Util::getBankAccountType(),
                            
                       
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style'     => 'background:#FFFF99',                
            )
        );
        $this->addElement($accounttype);
        
       
     
       //$bank_account_number = new Zend_Form_Element_Text('bank_account_number');
       $bank_account_number = new Zend_Form_Element_Password('beni_bank_account_number');
        $bank_account_number->setOptions(
            array(
                'label'      => 'Bank Account No. *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Alnum',
                                ),
                'renderPassword'    => TRUE,                
                'maxlength' => '20',
                'onpaste'=>"return false;",
                'autocomplete'=> 'off',
                'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($bank_account_number);
        
        
        $conf_bank_account_number = new Zend_Form_Element_Text('beni_conf_bank_account_number');
        $conf_bank_account_number->setOptions(
            array(
                'label'      => 'Confirm Bank Account No. *',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Alnum',
                                ),
                'renderPassword'    => TRUE,
                 'maxlength' => '20',
                 'onpaste'=>"return false;",
                'autocomplete'=> 'off',
                'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($conf_bank_account_number);
         $email = new Zend_Form_Element_Text('beni_email');
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
        
    }
    
}
?>
