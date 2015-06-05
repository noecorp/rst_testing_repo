<?php

//class RptAgentActivationForm extends Zend_Form
class RptAgentActivationForm extends App_Operation_Form
{
    


    
    public function init()
    {  
        //protected $_cancelLink = FALSE;
        $this->_cancelLink = false;
        
        $durationArr = Util::getDuration();
        
        $date_duration = $this->addElement('select', 'date_duration', array(
            'filters'    => array('StringTrim'),
            //'validators' => array('NotEmpty', array('StringLength', false, array(12, 20)),),
            'required'   => true,
            'label'      => 'Duration: *',
            'style'     => 'width:200px;',
            'multioptions'  => $durationArr,
        ));     
        
        
        //$durationArr = Util::getDuration();
        $objCity = new CityList();
        $citiesList = $objCity->getCityByStateCode();
        
        $statelist = new CityList();
        $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
//        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('dur',
//            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
//            'filters'    => array('StringTrim'),
//            //'validators' => array('NotEmpty', array('StringLength', false, array(10, 10)),),
//            'required'   => false,
//            'label'      => 'Date (e.g. dd-mm-yyyy) ',
//            'style'     => 'width:200px;',
//            'maxlength'  => '10',
//        )));    
        
                
        $state = new Zend_Form_Element_Select('state');
        $state->setOptions(
            array(
                'label'      => 'State: *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => $stateOptionsList,                       
            )
        );
        $this->addElement($state);
        
        $cities = new Zend_Form_Element_Select('city');
        $cities->setOptions(
            array(
                'label'      => 'City: ',
               
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'All Cities'),
            )
        );
        $cities->setRegisterInArrayValidator(false);
        $this->addElement($cities);  
        
        $cty = $this->addElement('hidden', 'cty', array(                
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
                    //'UiWidgetElement',
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
