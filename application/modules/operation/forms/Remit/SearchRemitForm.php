<?php

class Remit_SearchRemitForm extends App_Operation_Form
{
    
    public function init()
    { 
        $this->_cancelLink = false;
        
	$durationArr = Util::getDuration();

        $bankList = new Banks();
        $unicode  = array('300','400');
        $bankListOptions = $bankList->getBanksByUnicode($unicode);
        
	$prod = new Zend_Form_Element_Hidden('product');
        $prod->setOptions(array());
        $this->addElement($prod);
	
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
                'style' => 'width:215px;',
                'maxlength' => '100',
                 'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
	
	
	$product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(array(
	    'label'	    =>	'Product',
	    'required'	    =>	FALSE,
	    'filters'	    =>	array('StringTrim','StripTags'),
	    'validators'    =>	array('NotEmpty'),
            'multioptions'  =>	array('' =>'Select Product'),
	    'style'	    =>	'width:215px;',
	));
        $product->setRegisterInArrayValidator(false);
        $this->addElement($product);
	  
        $duration = $this->addElement('select', 'duration', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => false,
            'label'      => 'Duration: ',
            'style'     => 'width:215px;',
            'multioptions'     => $durationArr,
        )); 
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:210px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:210px;',)

        )); 
        
        $mobile = new Zend_Form_Element_Text('mobile_no');
        $mobile->setOptions(
            array(
                'label'      => 'Mobile No',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 10)),
                                ),
                'maxlength' => '10',
                'style'     => 'width: 210px'
            )
        );
        $this->addElement($mobile);
        
        $txnno = new Zend_Form_Element_Text('txn_no');
        $txnno->setOptions(
            array(
                'label'      => 'Txn No',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 10)),
                                ),
                'maxlength' => '10',
                'style'     => 'width: 210px'
            )
        );
        $this->addElement($txnno);
        
        $submit = new Zend_Form_Element_Submit('sub');
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
        
        $submit = new Zend_Form_Element_Submit('sub');
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
