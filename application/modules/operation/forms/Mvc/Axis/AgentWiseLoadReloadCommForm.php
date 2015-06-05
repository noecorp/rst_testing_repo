<?php

class Mvc_Axis_AgentWiseLoadReloadCommForm extends App_Operation_Form
{
    public function init()
    {  
        $this->_cancelLink = false;
        
        $objAU = new AgentUser();
        $str = STATUS_UNBLOCKED."', '".STATUS_BLOCKED."', '".STATUS_LOCKED;
       // $agentsArr = $objAU->getAgentsForDD(array('status'=>$str, 'enroll_status'=>ENROLL_APPROVED_STATUS, 'email_verified_status'=>EMAIL_VERIFIED_STATUS, 'agent_details_status'=>AGENT_ACTIVE_STATUS));
        $durationArr = Util::getDuration();
        
        $bankList = new Banks();
        $bankListOptions = $bankList->getCommProductBanks();
        
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
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
      
          
        $agent = new Zend_Form_Element_Select('id');
        $agent->setOptions(
            array(
                'label'      => 'Agent *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select Agent'),
            )
        );
        $agent->setRegisterInArrayValidator(false);
        $this->addElement($agent);  
        
        $agnt = new Zend_Form_Element_Hidden('aid');
        $agnt->setOptions(
            array()
        );
        $this->addElement($agnt);
        $duration = $this->addElement('select', 'duration', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => true,
            'label'      => 'Duration: *',
            'style'     => 'width:200px;',
            'multioptions'     => $durationArr,
        ));      

//         $btn = new Zend_Form_Element_Hidden('sub');
//        $btn->setOptions(
//            array(
//                'value' => '1'
//            )
//        );
//        $this->addElement($btn);
        
        $submit = new Zend_Form_Element_Submit('btn_submit');
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
