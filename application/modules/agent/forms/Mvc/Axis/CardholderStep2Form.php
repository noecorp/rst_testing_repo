<?php

class Mvc_Axis_CardholderStep2Form extends App_Agent_Form
{
    
   
    public function  init()
    {       
         $statelist = new CityList();
         $stateOptionsList = $statelist->getStateList($countryCode = 356);
  
        
        $address_line1 = $this->addElement('text', 'address_line1', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 45)),),
            'required'   => true,
            'label'      => 'Address Line1 *',
            'maxlength'  => '45',
        ));
        
        $address_line2 = $this->addElement('text', 'address_line2', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(2, 45)),),
            'required'   => true,
            'label'      => 'Address Line2 *',
            'maxlength'  => '45',
        ));
        
        
         $country = $this->addElement('select', 'country', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(2, 50)),),
            'required'   => true,
            'label'      => 'Country *',
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

                'required'   => True,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                
                'multioptions'    => array('' =>'Select City'),
            )
        );
        $city->setRegisterInArrayValidator(false);
        $this->addElement($city); 
               
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
        
        
       $alternate_contact_number = $this->addElement('text', 'alternate_contact_number', array(
            'filters'    => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(8, 12)),),
            'required'   => false,
            'label'      => 'Land Line Number',
            'maxlength'  => '12',
        )); 
       
       
       $educational_qualifications = $this->addElement('select', 'educational_qualifications', array(
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', array('StringLength', false, array(2, 100)),),
            'required'      => false,
            'label'         => 'Educational Qualification',
            'multioptions'  => Util::getEducationType(),
        ));
       
        
        
        $mother_m_name = new Zend_Form_Element_Text('mother_maiden_name');
        $mother_m_name->setOptions(
            array(
                'label'      => 'Mother Maiden Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(2, 25)),
                                ),
                'maxlength'  => '25',
            )
        );
        
        $this->addElement($mother_m_name);
        $mother_m_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
         $family_members = $this->addElement('select', 'family_members', array(
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1,2)),),
            'required'   => true,
            'label'      => 'Family Members *',
            'multioptions'  => Util::getFamilyMembers(),
              
        ));
       
        $shmart_rewards = $this->addElement('select', 'shmart_rewards', array(
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', array('StringLength', false, array(2, 3)),),
            'required'      => true,
            'label'         => 'Shmart Rewards *',
            'multioptions'  => Util::getYesNo(),
        ));       
          
          
           $offers_label = $this->addElement('hidden', 'offers_label', array(                
            'label'      => 'Offers *',
                'style'   => 'clear:both;'
              
        ));
          

            /*$offers = $this->createElement('multiCheckbox', 'offers');
            $offers->setLabel('Offers that you are interested in:');
            foreach(Zend_Registry::get("SHMART_REWARDS") as $key=>$value){
                    $offers->addMultiOption($key, $value);
            }
            $this->addElement($offers);          
          $rewards = Zend_Registry::get("SHMART_REWARDS");*/
      
          
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
            'label'         => 'Already Bank Account *',
            'multioptions'  =>  Util::getYesNo(),
        ));
        
          
        $vehicle_type = $this->addElement('select', 'vehicle_type', array(
            'filters'       => array('StringTrim'),
            'validators'    => array('NotEmpty', array('StringLength', false, array(2, 20)),),
            'required'      => false,
            'label'         => 'Vehicle Type',
            'multioptions'  => Util::getVehicleType(),
        ));
          
          
          
        $pin = $this->addElement('hidden', 'pin', array(                
            
                'style'   => 'clear:both;'
              
        ));
        
        /* temporarily commented for revert
        $discard = new Zend_Form_Element_Submit('btn_discard');
        $discard->setOptions(
            array(
                'label'      => 'Discard',
                'required'   => FALSE,
                'title'       => 'Discard',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($discard);
        
        $back = new Zend_Form_Element_Submit('btn_back');
        $back->setOptions(
            array(
                'label'      => 'Back',
                'required'   => FALSE,
                'title'       => 'Back',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($back);
        */
        
          $submit = new Zend_Form_Element_Submit('addch2');
        $submit->setOptions(
            array(
                'label'      => 'Enroll Cardholder',
                'required'   => FALSE,
                'title'       => 'Enroll Cardholder',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        /* temporarily commented for revert
        $next = new Zend_Form_Element_Submit('btn_next');
        $next->setOptions(
            array(
                'label'      => 'Next',
                'required'   => FALSE,
                'title'       => 'Next',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($next);
        */
        
        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        /*$this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));*/
        
        
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
