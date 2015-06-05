<?php

class CRNStatusForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        $statusArr = Util::getCRNStatusDropDown();
        
        $status = new Zend_Form_Element_Select('crn_status');
        $status->setOptions(
            array(
                'label'      => 'Status',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                'multioptions'    => $statusArr,         
            )
        );
        $this->addElement($status);
        
        $crn = new Zend_Form_Element_Text('crn');
        $crn->setOptions(
            array(
                'label'      => 'Card Number',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
            )
        );
        $this->addElement($crn);
        
        $cardPackId = new Zend_Form_Element_Text('card_pack_id');
        $cardPackId->setOptions(
            array(
                'label'      => 'Card Pack Id',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
            )
        );
        $this->addElement($cardPackId);
        
        $file = new Zend_Form_Element_Text('file');
        $file->setOptions(
            array(
                'label'      => 'File Name',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
            )
        );
        $this->addElement($file);
        
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
