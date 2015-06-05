<?php

class Corp_Boi_ConsolidatedReportForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        $user = Zend_Auth::getInstance()->getIdentity();   
        $durationArr = Util::getDuration();
       
        $bankBoi = App_DI_Definition_Bank::getInstance(BANK_BOI);
        $bankBoiUnicode = $bankBoi->bank->unicode;
        $productNSDC = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);
        $productNSDCUnicode = $productNSDC->product->unicode;
        $productModel = new Products();
        $productId = $productModel->getProductInfoByUnicode($productNSDCUnicode);
        
        
        
        $bcList = new Agents();
        
        $bcListOptions = $bcList->getTrainingPartnerList(array('status'=>STATUS_UNBLOCKED, 'enroll_status'=>STATUS_APPROVED,'product_id' => $productId['id']),$flgAll = TRUE);
        
        $agent = new Zend_Form_Element_Select('training_partner');
        $agent->setOptions(
            array(
                'label'      => 'Training Partner ',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $bcListOptions,         
            )
        );
        $agent->setRegisterInArrayValidator(false);
        $this->addElement($agent);
        
        $agentId = new Zend_Form_Element_Select('agent_id');
        $agentId->setOptions(
            array(
                'label'      => 'BC Name',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => array(''=>'Select BC'),         
            )
        );
        $agentId->setRegisterInArrayValidator(false);
        $this->addElement($agentId);
        
        
        $product = new Zend_Form_Element_Text('ref_num');
        $product->setOptions(
            array(
                'label'      => 'AOF Ref No.',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '50',
            )
        );
        $this->addElement($product);  

        $product = new Zend_Form_Element_Text('nsdc_enrollment_no');
        $product->setOptions(
            array(
                'label'      => 'NSDC Enrollment No.',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '50',
            )
        );
        $this->addElement($product); 
        
          
        $product = new Zend_Form_Element_Text('account_no');
        $product->setOptions(
            array(
                'label'      => 'Account No.',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '25',
            )
        );
        $this->addElement($product);  
        
        $product = new Zend_Form_Element_Text('aadhaar_no');
        $product->setOptions(
            array(
                'label'      => 'Aadhaar No.',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'maxlength' => '50',
            )
        );
        $this->addElement($product); 

        /* $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_approval',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'Checker Date: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  */
        
        $bankname = new Zend_Form_Element_Select('wallet_load_status');
        $bankname->setOptions(
            array(
                'label'      => 'Wallet Load Status',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => Util::getWalletStatusList(),         
            )
        );
        $this->addElement($bankname);
        
        $bankname = new Zend_Form_Element_Select('status');
        $bankname->setOptions(
            array(
                'label'      => 'Application Status',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => Util::getNsdcStatusList(),         
            )
        );
        $this->addElement($bankname);
        
        
        $duration = $this->addElement('select', 'dur', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => false,
            'label'      => 'Duration: ',
            'style'      => 'width:200px;',
            'multioptions' => $durationArr,
        ));      

          $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
        
         
        $prod = new Zend_Form_Element_Hidden('product');
        $prod->setOptions(
            array(
                'value' => $productId['id']
            )
        );
        $this->addElement($prod);
        
         $prod = new Zend_Form_Element_Hidden('agent');
        $prod->setOptions(
            array(
            )
        );
        $this->addElement($prod);
        
        $btn = new Zend_Form_Element_Hidden('sub');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
        $this->addElement($btn);
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required' => false,
                'ignore'   => true,
                'title'       => 'Submit',
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
        $this->setDecorators(array(
            'FormElements',
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
     
  
}
?>