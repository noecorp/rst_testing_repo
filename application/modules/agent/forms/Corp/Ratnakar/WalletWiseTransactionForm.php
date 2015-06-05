<?php

class Corp_Ratnakar_WalletWiseTransactionForm extends App_Agent_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        $user = Zend_Auth::getInstance()->getIdentity();   
//        $purseModel = new MasterPurse();
//        $productId = $user->product_id;
//          
//        $wallet = new Zend_Form_Element_Select('purse_master_id');
//        $wallet->setOptions(
//            array(
//                'label'      => 'Wallet *',
//                'multioptions'    => $purseModel->getPurseList($productId,'name'),
//                            
//                       
//                'required'   => true,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//                'validators' => array(
//                                    'NotEmpty',
//                                ),
//            )
//        );
//        $this->addElement($wallet);
//        
        
        $wallettype = new Zend_Form_Element_Select('wallet_type');
        $wallettype->setOptions(
            array(
                'label'      => 'Wallet Type*',               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => Util::walletType()
            )
        );
        $wallettype->setRegisterInArrayValidator(false);
        $this->addElement($wallettype);
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: (e.g. dd-mm-yyyy) *',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: (e.g. dd-mm-yyyy) *',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 
      

         
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
