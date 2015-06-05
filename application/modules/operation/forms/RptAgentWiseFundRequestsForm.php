<?php

//class RptAgentWiseFundRequestsForm extends Zend_Form
class RptAgentWiseFundRequestsForm extends App_Operation_Form
{
    public function init()
    {       
        $this->_cancelLink = false;
        
        $objAU = new AgentUser();
        $str = STATUS_UNBLOCKED."', '".STATUS_BLOCKED."', '".STATUS_LOCKED;
//        echo $str;exit;
        $agentsArr = $objAU->getAgentsForDD(array('status'=> $str, 'enroll_status'=>STATUS_APPROVED, 'agent_details_status'=>STATUS_ACTIVE));
        $durationArr = Util::getDuration();
        
        $agent_id = $this->addElement('select', 'id', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => true,
            'label'      => 'Agent: *',
            'style'     => 'width:200px;',
            'multioptions'     => $agentsArr,
        ));
        
        $duration = $this->addElement('select', 'dur', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => true,
            'label'      => 'Duration: *',
            'style'     => 'width:200px;',
            'multioptions'     => $durationArr,
        ));      

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
