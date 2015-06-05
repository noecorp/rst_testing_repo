<?php
/**
 * Add Hospital Form
 *
 * @category agent
 * @package agent_forms
 * @copyright Transerv
 */

class Corp_Ratnakar_AddHospitalForm extends App_Agent_Form
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
        $this->setMethod('post');
        
        $hospital_id_code = new Zend_Form_Element_Text('hospital_id_code');
        $hospital_id_code->setOptions(
            array(
                'label'      => 'Hospital Id *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('Digits',array('StringLength', false, array(5, 5)),),
                'maxlength' => '5',
            )
        );
        $this->addElement($hospital_id_code);
        
        $terminal_id_code = new Zend_Form_Element_Textarea('terminal_id_code');
        $terminal_id_code->setOptions(
            array(
                'label'      => 'Terminal Id',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'style' => 'height:100px;width:300px;',
                'validators' => array('StringLength',array('StringLength', false, array(0, 300)),),
                'maxlength' => '300',
            )
        );
        $this->addElement($terminal_id_code);
        
        $hospital_name = new Zend_Form_Element_Text('hospital_name');
        $hospital_name->setOptions(
            array(
                'label'      => 'Hospital Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(array('StringLength', false, array(2, 100)),),
                'maxlength' => '100',
            )
        );
        $this->addElement($hospital_name);
        
        $address = new Zend_Form_Element_Text('address');
        $address->setOptions(
            array(
                'label'      => 'Hospital Address *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(array('StringLength', false, array(2, 150)),),
                'maxlength' => '150',
            )
        );
        $this->addElement($address);
        
        $state = new Zend_Form_Element_Select('state');
        $state->setOptions(
            array(
                'label'      => 'State *',
                'required'   => true,
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
                'label'      => 'City *',
                'required'   => true,
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
        
        $pincode = new Zend_Form_Element_Select('pin_code');
        $pincode->setOptions(
            array(
                'label'      => 'Pincode *',

                'required'   => True,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'multioptions'    => array('' =>'Select Pincode'),
            )
        );
        $pincode->setRegisterInArrayValidator(false);
        $this->addElement($pincode); 
        
        
        $std_code = new Zend_Form_Element_Text('std_code');
        $std_code->setOptions(
            array(
                'label'      => 'STD Code',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array('Digits',array('StringLength', false, array(3, 6)),),
                'maxlength' => '6',
            )
        );
        $this->addElement($std_code);
        
        
        $phone = new Zend_Form_Element_Text('phone');
            $phone->setOptions(
                array(
                    'label'      => 'Phone Number',
                    'required'   => false,
                    'filters'    => array(
                                        'StringTrim',
                                        'StripTags',
                                    ),
                    'validators' => array('Digits',array('StringLength', false, array(5, 10)),),
                    'maxlength' => '10',
                )
            );
            $this->addElement($phone);
        
        $submit = new Zend_Form_Element_Submit('submit_form');
        $submit->setOptions(
            array(
                'label'      => 'Add Hospital',
                'required'   => FALSE,
                'ignore'   => true,
                'title'       => 'Add Hospital',
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
