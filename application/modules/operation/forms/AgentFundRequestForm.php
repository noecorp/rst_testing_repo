<?php

class AgentFundRequestForm extends App_Operation_Form
{
  
    public function  init()
    { 
        
        $responseStatus = Util::getFundResponseStatus();
       
        
       /* $arn = $this->addElement('text', 'amt', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 5)),),
            'required'   => true,
            'label'      => 'Amount: *',
            'style'     => 'width:200px;',
        ));
        
        $product_id = $this->addElement('select', 'fund_transfer_type_id', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Fund Transfer Type: *',
            'style'     => 'width:200px;',
            'multioptions' => $ftTypes,
        ));
        
        $comments = $this->addElement('textarea', 'comments', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 255)),),
            'required'   => false,
            'label'      => 'Agent Comments: *',
            'disabled'   => 'disabled',
            'style'     => 'width:400px;height:200px;',            
        ));
        
         */            
        $rescomments = $this->addElement('textarea', 'rescomments', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 255)),),
            'required'   => true,
            'label'      => 'Response Comments: *',
            //'disabled'   => 'disabled',
            'style'     => 'width:400px;height:200px;', 
             'maxlength' => '255',
        ));  
        
         $response_status = $this->addElement('select', 'response_status', array(
            'filters'    => array('StringTrim'),            
            'required'   => true,
            'label'      => 'Response Status: *',
            'style'     => 'width:200px;',
            'multioptions' => $responseStatus,
        ));
         
        $amt = $this->addElement('hidden', 'amt', array(        
        ));
        
         $btn_edit = $this->addElement('submit', 'btn_edit', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Add Response',
            'class'     => 'tangerine',
            'title'      => 'Add Response',
        ));
         
         
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
