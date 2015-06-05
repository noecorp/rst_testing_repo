<?php
/**
 * Form for adding new privileges in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

//class BankForm extends Zend_Form
class CorporateForm extends App_Operation_Form
{

    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        
        // set the form's method
        $this->setMethod('post');
       
       $statelist = new CityList();
       $stateOptionsList = $statelist->getStateList($countryCode = 356); 
        

     $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Corporate Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 100)),
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($name);
        
        $ecs_code = new Zend_Form_Element_Text('ecs_corp_id');
        $ecs_code->setOptions(
            array(
                'label'      => 'ECS Corporate Id *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
            )
        );
        $this->addElement($ecs_code);      
        
       
       
        
        
         $res_state = new Zend_Form_Element_Select('state');
        $res_state->setOptions(
            array(
                'label'      => 'State *',
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
        $this->addElement($res_state);

        
        $res_city = new Zend_Form_Element_Select('city');
        $res_city->setOptions(
            array(
                'label'      => 'City *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select City'),
            )
        );
        $res_city->setRegisterInArrayValidator(false);
        $this->addElement($res_city);              
        
   
        
        
        $address = new Zend_Form_Element_Text('address');
        $address->setOptions(
            array(
                'label'      => 'Address',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 250)),
                                ),
                'maxlength' => '250',
            )
        );
        $this->addElement($address);
        
        $pincode = new Zend_Form_Element_Select('pincode');
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
        
        $mobile1 = new Zend_Form_Element_Text('contact_number');
        $mobile1->setOptions(
            array(
                'label'      => 'Contact Number. *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(10, 10)),
                                ),
                    'maxlength' => '10',          
            )
        );
        $this->addElement($mobile1);
        
        $email = new Zend_Form_Element_Text('contact_email');
        $email->setOptions(
            array(
                'label'      => 'Contact Email *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','EmailAddress',array('StringLength', false, array(9, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($email);
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
        $city_name = new Zend_Form_Element_Hidden('city_name');
        $city_name->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($city_name);
        
        
        $pin = new Zend_Form_Element_Hidden('pin');
        $pin->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($pin);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Corporate',
                'required'   => FALSE,
                'title'       => 'Save Corporate',
                'class'     => 'tangerine',
                //'style'     => 'float: left; clear: both; word-wrap: normal;'
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