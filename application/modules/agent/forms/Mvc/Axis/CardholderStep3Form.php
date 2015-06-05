<?php

class Mvc_Axis_CardholderStep3Form extends App_Agent_Form
{
  
    public function  init()
    {
        
        $terms_conditions_label = $this->addElement('hidden', 'terms_conditions_label', array(                
            'label'      => 'Terms & Conditions *',
                'style'   => 'clear:both;'
              
        ));
          
        $terms_conditions_text = $this->addElement('textarea', 'terms_conditions_text', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 20)),),
            'required'   => false,
            'label'      => '',
            'disabled'   => 'disabled',
            'style'     => 'width:400px;height:200px;',
        ));
        
          $btnTC = $this->addElement('submit', 'btnTC', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Send Authorization Code',
            'class'     => 'tangerine',
        ));
          
        $terms_conditions_authcode = $this->addElement('text', 'terms_conditions_authcode', array(            
            'label'      => 'Terms & Conditions Auth Code *',
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', 'Digits',array('StringLength', false, array(6, 6)),),
            'required'      => true,
            'style'     => 'width:165px;',
            'maxlength'  => '6',
        ));
                
          $products_acknowledgement = new Zend_Form_Element_Checkbox('products_acknowledgement');
          $products_acknowledgement->setCheckedValue(FLAG_YES);
          $products_acknowledgement->setChecked(true);
          $products_acknowledgement->setUncheckedValue(FLAG_NO);
          $products_acknowledgement->setOptions(array(
                'label'      => 'Customer Acknowledgement for Product',
                'style'     => 'width:20px;',
                'readonly'    =>true
                
            ));
          $this->addElement($products_acknowledgement);
        
        
          $rewards_acknowledgement = new Zend_Form_Element_Checkbox('rewards_acknowledgement');
          $rewards_acknowledgement->setCheckedValue(FLAG_YES);
          $rewards_acknowledgement->setUncheckedValue(FLAG_NO);
          $rewards_acknowledgement->setChecked(true);

          $rewards_acknowledgement->setOptions(array(
                 'label'      => 'Customer Acknowledgement for Shmart Rewards',
                 'style'     => 'width:20px;',
                 'readonly'    =>true
                
            ));
          $this->addElement($rewards_acknowledgement);
         //$this->rewards_acknowledgement->setAttrib('value','sdfsd');
        
        /* temporarily commented for revert
        $discard = new Zend_Form_Element_Submit('btn_discard');
        $discard->setOptions(
            array(
                'label'      => 'Discard',
                'required'   => FALSE,
                'title'       => 'Discard',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($discard);
        
        $back = new Zend_Form_Element_Submit('btn_back');
        $back->setOptions(
            array(
                'label'      => 'Back',
                'required'   => FALSE,
                'title'       => 'Back',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($back);*/
        
         
        $submit = new Zend_Form_Element_Submit('addch3');
        $submit->setOptions(
            array(
                'label'      => 'Enroll Cardholder',
                'required'   => FALSE,
                'title'       => 'Enroll Cardholder',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        /* temporarily commented for revert
        $next = new Zend_Form_Element_Submit('btn_next');
        $next->setOptions(
            array(
                'label'      => 'Next',
                'required'   => FALSE,
                'title'       => 'Next',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($next); */
        
        
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        /*$this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));*/
        
        
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
