<?php

class AgentBalanceAlertForm extends App_Operation_Form
{
    
    public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
       
        $alert_id = new Zend_Form_Element_Hidden('id');
        $alert_id->setOptions(
            array(
                'label'      => '',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   
                                ),
               'maxlength' => '80',
            )
        );
        $this->addElement($alert_id);
        
        $agent_id = new Zend_Form_Element_Hidden('agent_id');
        $agent_id->setOptions(
            array(
                'label'      => '',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   
                                ),
               'maxlength' => '80',
            )
        );
        $this->addElement($agent_id);
        
       
   
        $min_amount_alert = new Zend_Form_Element_Text('min_amount_alert');
        $min_amount_alert->setOptions(
            array(
                'label'      => 'Agent Minimum Amount Alert*',
                'required'   => True,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        
        $this->addElement($min_amount_alert);
                 
        
        $submit = new Zend_Form_Element_Submit('sub');
        $submit->setOptions(
            array(
                'label'      => 'Save Alert Amount',
                'required'   => False,
                'title'       => 'Save Alert Amount',
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
