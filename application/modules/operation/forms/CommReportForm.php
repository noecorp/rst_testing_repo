<?php

//class CommReportForm extends Zend_Form
class CommReportForm extends App_Operation_Form
{
  
    public function init()
    {   
        $this->_cancelLink = false;
        
        
        $durationArr = Util::getDuration();
        
       
        
        $duration = $this->addElement('select', 'duration', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => true,
            'label'      => 'Duration: *',
            'style'     => 'width:200px;',
            'multioptions'     => $durationArr,
        )); 
        
        

 $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: *(e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        )); 

        
        
//        $btn = new Zend_Form_Element_Hidden('btn_submit');
//        $btn->setOptions(
//            array(
//                'value' => '1'
//            )
//        );
//        $this->addElement($btn);

       /*$btn_submit = $this->addElement('submit', 'btn_submit', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Submit',
            'title'       => 'Submit',
            'class'     => 'tangerine',
        ));*/
        
        $submit = new Zend_Form_Element_Submit('btn_submit');
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
