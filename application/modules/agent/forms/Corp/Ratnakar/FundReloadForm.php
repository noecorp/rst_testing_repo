<?php

class Corp_Ratnakar_FundReloadForm extends App_Agent_Form
{
  
    public function  init()
    {
        $this->setCancelLink(Util::formatURL("/mvc_axis_cardholderfund/cancel"));
       
       $product_id = $this->addElement('select', 'product_id', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 4)),),
            'required'   => false,
            'label'      => 'Cardholder Product *',
            'style'     => 'width:250px;',
            //'multioptions' => Util::getGender(),
        ));  
       
        $amount = $this->addElement('text', 'amount', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(1, 8)),),
            'required'   => true,
            'label'      => 'Load Amount *',
            'style'     => 'width:240px;',
            'maxlength'  => '7',
            'addRupeeSymbol' => true,
         //   'onblur'     => "javascript:agentFeeLoad();",
        ));  
        
//        $btn_auth_code = $this->addElement('submit', 'btn_auth_code', array(
//            'required' => false,
//            'ignore'   => true,
//            'label'    => 'Send Authorization Code',
//            //'onclick'     => "javascript:sendAuthCode();",
//            'class'     => 'tangerine',
//        )); 
        
//          $auth_code = $this->addElement('text', 'auth_code', array(
//            'filters'    => array('StringTrim'),
//            'validators' => array('NotEmpty', array('StringLength', false, array(4, 10)),),
//            'required'   => true,
//            'label'      => 'Authorization Code *',
//            'style'     => 'width:240px;',
//            'maxlength'  => '6',
//        ));
          
          
          
          $send_auth_code = $this->addElement('hidden', 'send_auth_code', array(
           // 'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            //'required'   => false,
            //'label'      => 'Mobile Number: *',
            //'style'     => 'width:200px;',
        ));

             $is_submit = $this->addElement('hidden', 'is_submit', array(
           // 'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
            //'required'   => false,
            //'label'      => 'Mobile Number: *',
            //'style'     => 'width:200px;',
        ));
          
           $submit = new Zend_Form_Element_Submit('btn_submit');
           $submit->setOptions(
            array(
                'label'      => 'Load Fund',
                'required'   => FALSE,
                'title'      => 'Load Fund',
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
