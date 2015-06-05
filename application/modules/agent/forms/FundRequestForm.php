<?php

class FundRequestForm extends App_Agent_Form
{
  
    public function  init()
    { 
        
        $objFTType = new FundTransferType();
        $ftTypes = $objFTType->getFundTransferTypeForDropDown();
        
        
        $arn = $this->addElement('text', 'amt', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty','Digits', array('StringLength', false, array(2, 8)),),
            'required'   => true,
            'label'      => 'Amount *',
            //'style'     => 'width:200px;',
            'maxlength'     => '7',
            'addRupeeSymbol' => true,
        ));
        
        $product_id = $this->addElement('select', 'fund_transfer_type_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Fund Transfer Type *',
//            'style'     => 'width:200px;',
            'multioptions' => $ftTypes,
        ));
        
        $comments = $this->addElement('textarea', 'comments', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 255)),),
            'required'   => true,
            'label'      => 'Comments *',
            //'disabled'   => 'disabled',
//            'style'     => 'width:400px;height:200px;',  
            'maxlength' =>  '255',
            'style'     => 'width:400px;height:200px;',            

        ));
                     
        /*$submit = new Zend_Form_Element_Submit('btn_send');
        $submit->setOptions(
            array(
                'label'      => 'Fund Request',
                'required'   => FALSE,                                
                'title'       => 'Fund Request',
                'class'     => 'tangerine',
            )
        );
        $submit->removeDecorator('label');
        $this->addElement($submit);*/
        
        $submit = new Zend_Form_Element_Submit('btn_send');
        $submit->setOptions(
            array(
                'label'      => 'Fund Request',
                'ignore'    => true,
                'required'   => FALSE,
                'title'       => 'Fund Request',
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
