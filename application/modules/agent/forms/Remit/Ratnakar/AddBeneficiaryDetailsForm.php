<?php
/*
 * Add Bene Form
 */
class Remit_Ratnakar_AddBeneficiaryDetailsForm extends App_Agent_Form
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
        $bankUniversalListOptions = $bankList->getUniverSalBank();
        $bankListOptions = array_merge($bankUniversalListOptions,$bankListOptions);
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
                  'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($name);
        $name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
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
                'style'     => 'background:#FFFF99',                
            )
        );
        $this->addElement($accounttype);
        
       
     
       //$bank_account_number = new Zend_Form_Element_Text('bank_account_number');
       $bank_account_number = new Zend_Form_Element_Password('bank_account_number');
        $bank_account_number->setOptions(
            array(
                'label'      => 'Bank Account No. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(5, 16))
                                ),
                'renderPassword'    => TRUE,                
                'maxlength' => '16',
                'onpaste'=>"return false;",
                'autocomplete'=> 'off',
                'style'     => 'background:#FFFF99',
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
                                    array('identical', false, array('token' => 'bank_account_number')),
                                    'NotEmpty','Digits', array('StringLength', false, array(5, 16))
                                ),
                'renderPassword'    => TRUE,
                 'maxlength' => '16',
                 'onpaste'=>"return false;",
                'autocomplete'=> 'off',
                'style'     => 'background:#FFFF99',
            )
        );
        $conf_bank_account_number->setErrorMessages(array('Bank Account Number do not match'));
        $this->addElement($conf_bank_account_number);
        
        $ifsc_code = new Zend_Form_Element_Text('ifsc_code');
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
                'maxlength' => '100',
                 'style' => 'width:210px;background:#FFFF99',
            )
        );
        $this->addElement($ifsc_code);
        
        $bankname = new Zend_Form_Element_Select('bank_name');
        $bankname->setOptions(
            array(
                'label'      => 'Bank Name *',
                'required'   => true,
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
        $bankstate = new Zend_Form_Element_Select('bank_state');
        $bankstate->setOptions(
            array(
                    'label'      => 'Branch State *',
                    'required'   => true,
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
        
        
        
        $branchcity = new Zend_Form_Element_Select('branch_city');
        $branchcity->setOptions(
            array(
                    'label'      => 'Branch City *',
                    'required'   => true,
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
       
        $branch_name = new Zend_Form_Element_Select('branch_name');
        $branch_name->setOptions(
            array(
                'label'      => 'Branch Name *',
                'required'   => true,
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
        
        
  
        $bank_area = new Zend_Form_Element_Text('branch_address');
        $bank_area->setOptions(
            array(
                'label'      => 'Branch Address *',
                'required'   => true,
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
               // 'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                    'Digits',array('StringLength', false, array(6, 6)),
                                ),
                 'maxlength' => '6',
                 'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($code);       
        
       $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
          
        ));
       
       $ifsc = $this->addElement('hidden', 'ifsc', array(
          
        ));
        
        $this->addElement('hidden', 'is_submit', array('value' => "0"));
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
        
	
		
		// transfer data
		// input field for entering amount
	/*	$transfer_amount = new Zend_Form_Element_Text('transfer_amount');
        $transfer_amount->setOptions(
            array(
                'label'      => 'Amount *',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                 'minlength' => '3',
                 'style'     => 'background:#FFFF99',
            )
        );
        $this->addElement($transfer_amount);  
		
		// radio button for transation type
		$this->addElement('radio', 'transfer_falg', array(
			'label'=>'Pl select transfer mode, enter amount and click on Submit to initiate transfer to beneficiary registered',
			'multiOptions'=>array(
				'NEFT' => 'NEFT',
				'IMPS' => 'IMPS',
			),
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
		)); 
		
		
					// submit button
        $submit_ammount = new Zend_Form_Element_Submit('submit_ammount');
        $submit_ammount->setOptions(
            array(
                'label'      => 'Transfer',
                'required'   => FALSE,
                'title'       => 'Transfer',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit_ammount);
	*/	
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
        
		
		
		
/*		  $this->addDisplayGroup(array(
            'name',
			'csrfhash',
			'formName',
            'bank_account_type',
			'conf_bank_account_number',
            'bank_account_number',
            'ifsc_code',
            'bank_name',
            'bank_state',
            'branch_city',
			'send_auth_code', 
			'ifsc',
			'is_submit',
            'branch_name',
            'branch_address',
            'submit_form',
			'auth_code',
			'hidden',
			'btn_auth_code',
			'submit_ammount',
            'transfer_amount',
            'transfer_falg',
            'submit_ammount',
			
            
        ), 'beneficiary', array(
            'legend' => 'Beneficiary Information'
        ));*/
        
       // $remitter = $this->getDisplayGroup('beneficiary');

        
        
    }
     
    
    
    
}
?>
