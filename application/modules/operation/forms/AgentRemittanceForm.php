<?php

class AgentRemittanceForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        $durationArr = Util::getDuration();
        $bankList = new Banks();
       // $bankListOptions = $bankList->getCustomerConceptBanks();
        $bankKotak = App_DI_Definition_Bank::getInstance(BANK_KOTAK);
        $bankKotakUnicode = $bankKotak->bank->unicode;
       // $bankListOptions = $bankList->getRemitProductBanks($bankKotakUnicode);
        $bankListOptions = $bankList->getRemitProductBanks();
        $bankInfo = $bankList->getBankbyUnicode($bankKotakUnicode);
        $bankname = new Zend_Form_Element_Select('bank_unicode');
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
                'maxlength' => '100',
                 'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
        
       // $productModel = new Products();
       // $productOptionsArr = $productModel->getBankProgramProducts('', PROGRAM_TYPE_REMIT);
       //$productOptionsArr = array('Select Product');
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Product Name *',
                'multioptions'   => array('' => 'Select Product'),

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
        $product->setRegisterInArrayValidator(false);
        $this->addElement($product);
        
        
        
        $searchCriteria = new Zend_Form_Element_Select('searchCriteria');
        $searchCriteria->setOptions(
            array(
                'label'      => 'Search Criteria *',
                'multioptions'    => Util::getAgentSearchCriteria(),
                            
                       
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
        $this->addElement($searchCriteria);
           

        $keyword = new Zend_Form_Element_Text('keyword');
        $keyword->setOptions(
            array(
                'label'      => 'Value *',
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
        $this->addElement($keyword);
  
         
        $prod = new Zend_Form_Element_Hidden('product');
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
