<?php

class Remit_Ratnakar_NeftResponseForm extends App_Operation_Form
{
    public function init()
    {  
        $this->_cancelLink = false;
        
        $objRemitReq = new Remit_Ratnakar_Remittancerequest();
        $batchNameArr = $objRemitReq->getNeftBatchForDD(STATUS_PROCESSED);
        
        $batch_name = $this->addElement('select', 'batch_name', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => false,
            'label'      => 'Batch Name: *',
            'style'     => 'width:200px;',
            'multioptions'     => $batchNameArr,
            
        ));
        
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
