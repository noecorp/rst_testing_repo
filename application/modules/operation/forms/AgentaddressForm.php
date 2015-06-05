<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentaddressForm extends App_Operation_Form
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
        
        // set the form's method
        $this->setMethod('post');
        
        $statelist = new CityList();
        $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
       $profile_pic = new Zend_Form_Element_File('profile_pic');
       $profile_pic->setLabel('Profile Photo Path')
	         ->setRequired(false)
                 ->addValidator(new Zend_Validate_File_Size('5MB'));
       $this->addElement($profile_pic);
       
       
        $res_type = new Zend_Form_Element_Select('res_type');
        $res_type->setOptions(
            array(

                'label'      => 'Resident Type *',
                'multioptions'    => Util::getResidentType(), 

                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($res_type);
        
   
        
        $res_country = new Zend_Form_Element_Select('res_country');
        $res_country->setOptions(
            array(
                'label'      => 'Country *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => Util::getCountry(),                       
                
            )
        );
        $this->addElement($res_country);
        

        $res_state = new Zend_Form_Element_Select('res_state');
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

        
        $res_city = new Zend_Form_Element_Select('res_city');
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
        
        $res_address1 = new Zend_Form_Element_Text('res_address1');
        $res_address1->setOptions(
            array(
                'label'      => 'Address Line 1 *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                 'maxlength' => '50',
            )
        );
        $this->addElement($res_address1);
        
        
        
        $res_address2 = new Zend_Form_Element_Text('res_address2');
        $res_address2->setOptions(
            array(
                'label'      => 'Address Line 2 ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_address2);
        
        
      
        
        
        

        
        
        $res_taluka = new Zend_Form_Element_Text('res_taluka');
        $res_taluka->setOptions(
            array(
                'label'      => 'Taluka ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_taluka);
        
        
        
        
        $res_district = new Zend_Form_Element_Text('res_district');
        $res_district->setOptions(
            array(
                'label'      => 'District',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_district);
        
        
        
        
     
        
        
        
       $pincode = new Zend_Form_Element_Select('res_pincode');
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
        
        /*$note = new Zend_Form_Element_Note(
            'office_add',
             array('value' => 'Office Address') 
                    );
        $this->addElement($note);*/
        
        $res_address1 = new Zend_Form_Element_Text('estab_name');
        $res_address1->setOptions(
            array(
                'label'      => 'Office Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_address1);
        
         $res_country = new Zend_Form_Element_Select('estab_country');
        $res_country->setOptions(
            array(
                'label'      => 'Office Country *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => Util::getCountry(),                       
                
            )
        );
        $this->addElement($res_country);
        

        $res_state = new Zend_Form_Element_Select('estab_state');
        $res_state->setOptions(
            array(
                'label'      => 'Office State *',
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

        
        $res_city = new Zend_Form_Element_Select('estab_city');
        $res_city->setOptions(
            array(
                'label'      => 'Office City *',
               
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
        
        $res_address1 = new Zend_Form_Element_Text('estab_address1');
        $res_address1->setOptions(
            array(
                'label'      => 'Office Address Line 1 *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_address1);
        
        
        
        $res_address2 = new Zend_Form_Element_Text('estab_address2');
        $res_address2->setOptions(
            array(
                'label'      => 'Office Address Line 2 ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_address2);
        
        
      
        
        
        

        
        
        $res_taluka = new Zend_Form_Element_Text('estab_taluka');
        $res_taluka->setOptions(
            array(
                'label'      => 'Office Taluka ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_taluka);
        
        
        
        
        $res_district = new Zend_Form_Element_Text('estab_district');
        $res_district->setOptions(
            array(
                'label'      => 'Office District',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($res_district);
        
        
        
        
     
        
        
        
       $pincode = new Zend_Form_Element_Select('estab_pincode');
        $pincode->setOptions(
            array(
                'label'      => 'Office Pincode *',

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
       
        
        
        $city = new Zend_Form_Element_Hidden('city');
        $city->setOptions(
            array(
               'validators' => array(
                                    'NotEmpty'
                                ),
                
            )
        );
       
        $this->addElement($city);  
         $city = new Zend_Form_Element_Hidden('es_city');
        $city->setOptions(
            array(
               'validators' => array(
                                    'NotEmpty'
                                ),
                
            )
        );
       
        $this->addElement($city);  
        
        $agent_detail_id = new Zend_Form_Element_Hidden('agent_detail_id');
        $agent_detail_id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($agent_detail_id);
        
        $pin = $this->addElement('hidden', 'pin', array(                
            
                'style'   => 'clear:both;'
              
        ));
           
         $pin = $this->addElement('hidden', 'estab_pin', array(                
            
                'style'   => 'clear:both;'
              
        ));
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Address Details',
                'required'   => FALSE,
                'title'       => 'Save Address Details',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column')),
                    //array('Label',array('tag'=>'div')),
                   // array(array('row'=>'HtmlTag'),array('tag'=>'div','class'=>'formrow')),
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