<?php

class Mvc_Axis_CardholderStep3Form extends App_Operation_Form
{
  
    public function  init()
    {
                        
          $products_acknowledgement = new Zend_Form_Element_Checkbox('products_acknowledgement');
          $products_acknowledgement->setCheckedValue(FLAG_YES);
          $products_acknowledgement->setUncheckedValue(FLAG_NO);
          $products_acknowledgement->setOptions(array(
                'label'      => 'Customer Acknowledgement for Product:',
                'style'     => 'width:20px;',       
            ));
          $this->addElement($products_acknowledgement);
        
        
          $rewards_acknowledgement = new Zend_Form_Element_Checkbox('rewards_acknowledgement');
          $rewards_acknowledgement->setCheckedValue(FLAG_YES);
          $rewards_acknowledgement->setUncheckedValue(FLAG_NO);
          $rewards_acknowledgement->setOptions(array(
                 'label'      => 'Customer Acknowledgement for Shmart Rewards:',
                 'style'     => 'width:20px;'        
            ));
          $this->addElement($rewards_acknowledgement);
         //$this->rewards_acknowledgement->setAttrib('value','sdfsd');
        
         
           
        $submit = new Zend_Form_Element_Submit('addch3');
        $submit->setOptions(
            array(
                'label'      => 'Edit Details',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Edit Details',
            )
        );
        $this->addElement($submit); 
        
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
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
