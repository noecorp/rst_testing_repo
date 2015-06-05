<?php

//class CommReportForm extends Zend_Form
class AgentwiseFeeReportForm extends App_Operation_Form
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
        
         
        $agent = new Zend_Form_Element_Select('agent_id');
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
        
        $duration = $this->addElement('select', 'duration', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => true,
            'label'      => 'Duration: *',
            'style'     => 'width:200px;',
            'multioptions'     => $durationArr,
        )); 
         $agnt = new Zend_Form_Element_Hidden('id');
        $agnt->setOptions(
            array()
        );
        $this->addElement($agnt);
        $btn = new Zend_Form_Element_Hidden('btn_submit');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
        $this->addElement($btn);

       /*$btn_submit = $this->addElement('submit', 'btn_submit', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Submit',
            'title'       => 'Submit',
            'class'     => 'tangerine',
        ));*/
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required' => false,
                'ignore'   => true,
                'title'       => 'Submit',
                'class'     => 'tangerine'
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
