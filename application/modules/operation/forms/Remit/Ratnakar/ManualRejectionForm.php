<?php

class Remit_Ratnakar_ManualRejectionForm extends App_Operation_Form
{
    public function init()
    {  
        $this->_cancelLink = false;
        
        $utr = new Zend_Form_Element_Text('utr');
        $utr->setOptions(
            array(
                'label'      => 'UTR Number',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 20)),
                                ),
                'maxlength' => '20',
            )
        );
        $this->addElement($utr);
        
        
        $utr = new Zend_Form_Element_Text('txn_code');
        $utr->setOptions(
            array(
                'label'      => 'Transaction Reference Number',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 20)),
                                ),
                'maxlength' => '20',
            )
        );
        $this->addElement($utr);
        
        
        
        $rejection_code = new Zend_Form_Element_Text('rejection_code');
        $rejection_code->setOptions(
            array(
                'label'      => 'Rejection Code',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 30)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($rejection_code);
        
        
        $rejection_remark = new Zend_Form_Element_Text('rejection_remark');
        $rejection_remark->setOptions(
            array(
                'label'      => 'Rejection Remarks',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 30)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($rejection_remark);
        
        
        
       
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
