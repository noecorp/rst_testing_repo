<?php
/**
 * Search Hospital Form
 *
 * @category agent
 * @package agent_forms
 * @copyright Transerv
 */

class Corp_Ratnakar_HospitalSearchForm extends App_Agent_Form
{
    /**
     * This form does not have a cancel link
     * 
     * @var mixed
     * @access protected
     */
    protected $_cancelLink = false;
    
    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        
        $statelist = new CityList();
        $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
        // set the form's method
        //$this->setMethod('get');
        
        $hospital_id_code = new Zend_Form_Element_Text('hospital_id_code');
        $hospital_id_code->setOptions(
            array(
                'label'      => 'Hospital Id',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('Digits',array('StringLength', false, array(5, 5)),),
                'maxlength' => '5',
            )
        );
        $this->addElement($hospital_id_code);
        
        $terminal_id_code = new Zend_Form_Element_Text('terminal_id_code');
        $terminal_id_code->setOptions(
            array(
                'label'      => 'Terminal Id',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('Digits',array('StringLength', false, array(5, 8)),),
                'maxlength' => '8',
            )
        );
        $this->addElement($terminal_id_code);
        
        $hospital_name = new Zend_Form_Element_Text('hospital_name');
        $hospital_name->setOptions(
            array(
                'label'      => 'Hospital Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(array('StringLength', false, array(2, 150)),),
                'maxlength' => '100',
            )
        );
        $this->addElement($hospital_name);
        
        $pin_code = new Zend_Form_Element_Text('pin_code');
        $pin_code->setOptions(
            array(
                'label'      => 'Pin Code',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('Digits',array('StringLength', false, array(6, 6)),),
                'maxlength' => '6',
            )
        );
        $this->addElement($pin_code);
        
        $state = new Zend_Form_Element_Select('state');
        $state->setOptions(
            array(
                'label'      => 'State',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                //'validators' => array(),
                'multioptions'    => $stateOptionsList,                       
            )
        );
        $this->addElement($state);
        
        
        $city = new Zend_Form_Element_Select('city');
        $city->setOptions(
            array(
                'label'      => 'City',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                //'validators' => array(''),
                'multioptions'    => array('' =>'Select City'),
            )
        );
        //$res_city->setRegisterInArrayValidator(false);
        $this->addElement($city);
        
        
//        $subbtn = new Zend_Form_Element_Hidden('formsub');
//        $subbtn->setOptions(
//            array(
//                'value' => '1'
//            )
//        );
//        $this->addElement($subbtn);
        
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Search Hospital',
                'required'   => FALSE,
                'ignore'   => true,
                'title'       => 'Search Hospital',
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
                // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            //array('HtmlTag', array('tag' => 'div', 'class' => 'innerbox')),
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
}