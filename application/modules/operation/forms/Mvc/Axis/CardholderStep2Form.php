<?php

class Mvc_Axis_CardholderStep2Form extends App_Operation_Form
{
  
    public function  init()
    {      
        
        $statelist = new CityList();
        $stateOptionsList = $statelist->getStateList($countryCode = 356);
        
        $address_line1 = $this->addElement('text', 'address_line1', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'   => true,
            'label'      => 'Address Line1: *',
            'style'     => 'width:200px;',
        ));
        
        $address_line2 = $this->addElement('text', 'address_line2', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Address Line2:',
            'style'     => 'width:200px;',
        ));
        
        
         $country = $this->addElement('select', 'country', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Country: *',
            'style'     => 'width:200px;',
            'multioptions' => Util::getCountry(),
        ));                 
        
         
        $state = new Zend_Form_Element_Select('state');
        $state->setOptions(
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
              // 'multioptions'    => Util::getStates(),             
              'multioptions'    => $stateOptionsList,                        
           )
       );
       $this->addElement($state);
        
        $city = new Zend_Form_Element_Select('city');
        $city->setOptions(
            array(
                'label'      => 'City *',

                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'multioptions'    => array('' =>'Select City'),
            )
        );
        $city->setRegisterInArrayValidator(false);
        $this->addElement($city); 
               
         $city = new Zend_Form_Element_Select('pincode');
        $city->setOptions(
            array(
                'label'      => 'Pin Code *',

                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'multioptions'    => array('' =>'Select Pincode'),
            )
        );
        $city->setRegisterInArrayValidator(false);
        $this->addElement($city); 
        
       $alternate_contact_number = $this->addElement('text', 'alternate_contact_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 20)),),
            'required'   => false,
            'label'      => 'Land Line Number: ',
            'style'     => 'width:200px;',
        )); 
       
       
       $educational_qualifications = $this->addElement('select', 'educational_qualifications', array(
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', array('StringLength', false, array(2, 100)),),
            'required'      => false,
            'label'         => 'Educational Qualification: *',
            'style'         => 'width:200px;',
            'multioptions'  => Util::getEducationType(),
        ));
       
        $mother_maiden_name = $this->addElement('text', 'mother_maiden_name', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(2, 20)),),
            'required'   => false,
            'label'      => 'Mother Maiden Name:',
            'style'     => 'width:200px;',
        ));
        
         $family_members = $this->addElement('select', 'family_members', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1,2)),),
            'required'   => true,
            'label'      => 'Family Members: *',
            'style'     => 'width:200px;',
            'multioptions'  => Util::getFamilyMembers(),
              
        ));
       
        $shmart_rewards = $this->addElement('select', 'shmart_rewards', array(
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', array('StringLength', false, array(2, 100)),),
            'required'      => true,
            'label'         => 'Shmart Rewards: *',
            'style'         => 'width:200px;',
            'multioptions'  => Util::getYesNo(),
        )); 
        
           
          
           $offers_label = $this->addElement('hidden', 'offers_label', array(                
            'label'      => 'Offers: *',
                'style'   => 'clear:both;'
              
        ));
                   
          $is_book = new Zend_Form_Element_Checkbox('is_book');
          $is_book->setCheckedValue(FLAG_YES);
          $is_book->setUncheckedValue(FLAG_NO);
          $is_book->setOptions(array(
                'label'      => 'Books',
                'style'     => 'width:14px;'        
            ));
          $this->addElement($is_book);
          
          
          $is_travel = new Zend_Form_Element_Checkbox('is_travel');
          $is_travel->setCheckedValue(FLAG_YES);
          $is_travel->setUncheckedValue(FLAG_NO);
          $is_travel->setOptions(array(
                'label'      => 'Travel',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_travel);
          
          
          $is_movies = new Zend_Form_Element_Checkbox('is_movies');
          $is_movies->setCheckedValue(FLAG_YES);
          $is_movies->setUncheckedValue(FLAG_NO);
          $is_movies->setOptions(array(
                'label'      => 'Movies',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_movies);          
          
          $is_shopping = new Zend_Form_Element_Checkbox('is_shopping');
          $is_shopping->setCheckedValue(FLAG_YES);
          $is_shopping->setUncheckedValue(FLAG_NO);
          $is_shopping->setOptions(array(
                'label'      => 'Shopping',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_shopping);
          
          $is_electronics = new Zend_Form_Element_Checkbox('is_electronics');
          $is_electronics->setCheckedValue(FLAG_YES);
          $is_electronics->setUncheckedValue(FLAG_NO);
          $is_electronics->setOptions(array(
                'label'      => 'Electronic',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_electronics);
            
          $is_music = new Zend_Form_Element_Checkbox('is_music');
          $is_music->setCheckedValue(FLAG_YES);
          $is_music->setUncheckedValue(FLAG_NO);
          $is_music->setOptions(array(
                'label'      => 'Music',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_music);
            
          $is_automobiles = new Zend_Form_Element_Checkbox('is_automobiles');
          $is_automobiles->setCheckedValue(FLAG_YES);
          $is_automobiles->setUncheckedValue(FLAG_NO);
          $is_automobiles->setOptions(array(
                'label'      => 'Automobiles',
                'style'     => 'width:14px;'        
          ));
          $this->addElement($is_automobiles);
              
           
                    
          $already_bank_account = $this->addElement('select', 'already_bank_account', array(
            'filters'       => array('StringTrim'),
            'validators'    => array(array('StringLength', false, array(2, 5)),),
            'required'      => true,
            'label'         => 'Already Bank Account: *',
            'style'         => 'width:200px;',
            'multioptions'  =>  Util::getYesNo(),
        ));
        
          
        $vehicle_type = $this->addElement('select', 'vehicle_type', array(
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'      => false,
            'label'         => 'Vehicle Type:',
            'style'         => 'width:200px;',
            'multioptions'  => Util::getVehicleType(),
        ));
          
           
        
        $submit = new Zend_Form_Element_Submit('addch2');
        $submit->setOptions(
            array(
                'label'      => 'Edit Details',
                'required'   => FALSE,
                'class'     => 'tangerine',
                'title'      => 'Edit Details',
            )
        );
        $this->addElement($submit); 
        
        $shmart_rewards_old = $this->addElement('hidden', 'shmart_rewards_old', array(              
        ));
        
        
        $pin = $this->addElement('hidden', 'pin', array(                
            
                'style'   => 'clear:both;'
              
        ));
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
?>
