<?php
/*
 * Add Bene Form
 */
class Remit_Ratnakar_DeactivateBeneficiaryForm extends App_Agent_Form
{

     public function init() {
        // init the parent
        parent::init();
        
        $this->setMethod('post');

        
 
        
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
        
     
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Delete Beneficiary',
                'required'   => FALSE,
                'title'       => 'Delete Beneficiary',
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
