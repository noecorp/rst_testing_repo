<?php

class Corp_Kotak_LoadReportForm extends App_Corporate_Form
{
    
    public function init()
    {   
        /*
        
        $this->_cancelLink = false;
        $user = Zend_Auth::getInstance()->getIdentity();   
        $durationArr = Util::getDuration();
        $bcList = new Agents();
        $bcListOptions = $bcList->getBCList(array('status'=>STATUS_UNBLOCKED, 'enroll_status'=>STATUS_APPROVED, 'user_id' => $user->id,'user_type' =>$user->user_type),$flgAll = TRUE);
        
        */
         //$statusArr = Util::getCardHolderStatusList();
        
        $statusArr = Util::getCardLoadStatusList();
        unset($statusArr[STATUS_CUTOFF]);
        $this->_cancelLink = false;
        
        $durationArr = Util::getDuration();
       
        $product = new Zend_Form_Element_Select('product_id');
        $product->setOptions(
            array(
                'label'      => 'Product Name *',
                'multioptions'    => array('' => 'Select Product'),

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style'     => 'width:210px;',
            )
        );
        $this->addElement($product);
        
        $status = new Zend_Form_Element_Select('status');
        $status->setOptions(
            array(
                'label'      => 'Status *',
                'required'   => TRUE,
                'multioptions'    => $statusArr,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style'     => 'width:210px;',
            )
        );
        $this->addElement($status);
       
     

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
        
        // Department Search : employer_name : Start Code
         
        $department = new Zend_Form_Element_Text('employer_name');
        $department->setOptions(
            array(
                'label'      => 'Department',
                'required'   => false,
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
        $this->addElement($department);
       
         
         
       
        // Department Seach Criteria Name : End Code
        
        // Employee Location : Start Code
        
        $keyword = new Zend_Form_Element_Text('employer_loc');
        $keyword->setOptions(
            array(
                'label'      => 'Location',
                'required'   => false,
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
        
       // Employee Location : End Code 
           
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
