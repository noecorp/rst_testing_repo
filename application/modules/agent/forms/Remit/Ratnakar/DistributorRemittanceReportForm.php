<?php
/*
 * Remittance Report
 */
class Remit_Ratnakar_DistributorRemittanceReportForm extends App_Agent_Form
{
    
    public function init()
    { 
        $this->_cancelLink = false;
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentList = array();
        $agentModel = new Agents();
        $agentList = $agentModel->getAgentNameCodeList(
                 array('status' => STATUS_UNBLOCKED, 
                        'enroll_status' => STATUS_APPROVED,
                        'user_id' => $user->id, 
                        'user_type' => $user->user_type, 
                        'ret_type' => 'arr',
                        'blocked_status' => STATUS_BLOCKED));
        
             
        $durationArr = Util::getDuration();
        $duration = $this->addElement('select', 'dur', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => false,
            'label'      => 'Duration: ',
            'style'      => 'width:250px;',
            'multioptions' => $durationArr,
        ));     
        
   $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:250px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:250px;',)

        )); 
        
        $agent = new Zend_Form_Element_Select('agent_id');
        $agent->setOptions(
            array(
                'label'      => 'Agent',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'style'      => 'width:250px;',
                'multioptions'    => $agentList,
            )
        );
        $agent->setRegisterInArrayValidator(false);
        $this->addElement($agent);  
       
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
