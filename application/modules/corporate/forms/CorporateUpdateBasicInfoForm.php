<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class CorporateUpdateBasicInfoForm extends App_Corporate_Form
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
       
        $title = new Zend_Form_Element_Select('title');
        $title->setOptions(
            array(
                'label'      => 'Title *',
                'multioptions'    => Util::getTitle(),
                            
                       
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
        $this->addElement($title);
        
       $first_name = new Zend_Form_Element_Text('first_name');
        $first_name->setOptions(
            array(
                'label'      => 'First Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
            )
        );
        $this->addElement($first_name);
        $first_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
        
        $middle_name = new Zend_Form_Element_Text('middle_name');
        $middle_name->setOptions(
            array(
                'label'      => 'Middle Name',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(0, 35)),
                                ),
                   'maxlength' => '35',
            )
        );
        $this->addElement($middle_name);
        $middle_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
        
        $last_name = new Zend_Form_Element_Text('last_name');
        $last_name->setOptions(
            array(
                'label'      => 'Last Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(1, 35)),
                                ),
                   'maxlength' => '35',
            )
        );
        $this->addElement($last_name);
        $last_name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
        
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
                'multioptions'    => $stateOptionsList
,                       
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
                                    'NotEmpty',
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
                                    'NotEmpty',
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
                                    'NotEmpty'
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
                                    'NotEmpty'
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
        
        $city = new Zend_Form_Element_Hidden('city');
        $city->setOptions(
            array(
               'validators' => array(
                                    'NotEmpty'
                                ),
                
            )
        );
       
        $this->addElement($city);
        
        $pin = $this->addElement('hidden', 'pin', array(                
            
                'style'   => 'clear:both;'
              
        ));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Basic Details',
                'required'   => FALSE,
                'title'       => 'Save Basic Details',
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