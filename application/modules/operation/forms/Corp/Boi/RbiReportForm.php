<?php

class Corp_Boi_RbiReportForm extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        
        $months =  Util::getMonths();
        $years =  Util::getYears();
        
              
        $month = new Zend_Form_Element_Select('month');
        $month->setOptions(  
            array(
                'label'      => 'Month *',
                'required'   => TRUE,
                'value' => 3,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $months,         
            )
        )
        ->setValue(3);
        
        $this->addElement($month);
        
        $year = new Zend_Form_Element_Select('year');
        $year->setOptions(  
            array(
                'label'      => 'Year *',
                'required'   => TRUE,
                'value' => date('Y'),
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $years,         
            )
        );
        $this->addElement($year);
     
        
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
