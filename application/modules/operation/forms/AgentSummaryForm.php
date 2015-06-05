<?php

class AgentSummaryForm extends App_Operation_Form
{
    public function init()
    {  
        $this->_cancelLink = false;
        
        $bankList = new Banks();
        $bankListOptions = $bankList->getRemitProductBanks();
        
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
        $durationArr = Util::getDuration();
        
        $agent = new Zend_Form_Element_Select('id');
        $agent->setOptions(
            array(
                'label'      => 'Agent *',
               
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select Agent'),
                'style'         => 'width:210px;',
            )
        );
        $agent->setRegisterInArrayValidator(false);
        $this->addElement($agent); 
         
        $duration = $this->addElement('select', 'dur',
            array(
                'filters'       =>  array('StringTrim'),
                'required'      =>  false,
                'label'         =>  'Duration ',
                'style'         =>  'width:210px;',
                'multioptions'  =>  $durationArr,
        ));      

        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array(
                'jQueryParams'  =>  array('dateFormat' => 'dd-mm-yy'),
                'filters'       =>  array('StringTrim'),
                'validators'    =>  array('NotEmpty', array('StringLength', false, array(10, 20)),),
                'required'      =>  false,
                'label'         =>  'From: (e.g. dd-mm-yyyy) ',
                'maxlength'     =>  '20',
                'style'         =>  'width:200px;',)
        ));  
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array(
                'jQueryParams'  =>  array('dateFormat' => 'dd-mm-yy'),
                'filters'       =>  array('StringTrim'),
                'validators'    =>  array('NotEmpty', array('StringLength', false, array(10, 20)),),
                'required'      =>  false,
                'label'         =>  'To: (e.g. dd-mm-yyyy) ',
                'maxlength'     =>  '20',
                'style'         =>  'width:200px;',)

        )); 
        
        $agnt = new Zend_Form_Element_Hidden('agent_id');
        $agnt->setOptions(
            array()
        );
        $this->addElement($agnt);
        
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