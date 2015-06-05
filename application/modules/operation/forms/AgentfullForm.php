<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentfullForm extends App_Operation_Form
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
        
        $afn = new Zend_Form_Element_Text('afn');
        $afn->setOptions(
            array(
                'label'      => 'AFN *',
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
        $this->addElement($afn);
        
        
        
        
        
        $email = new Zend_Form_Element_Text('email');
        $email->setOptions(
            array(
                'label'      => 'Email *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','EmailAddress'
                                ),
            )
        );
        $this->addElement($email);
        
        
        
        $username = new Zend_Form_Element_Text('username');
        $username->setOptions(
            array(
                'label'      => 'Username *',
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
        $this->addElement($username);
        
        
        
        
        
        
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
                                    'NotEmpty'
                                ),
            )
        );
        $this->addElement($first_name);
        
        
        
        
        
        $middle_name = new Zend_Form_Element_Text('middle_name');
        $middle_name->setOptions(
            array(
                'label'      => 'Middle Name',
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
        $this->addElement($middle_name);
        
        
        
        
        
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
                                    'NotEmpty'
                                ),
            )
        );
        $this->addElement($last_name);
        
        
        
        
        $home = new Zend_Form_Element_Text('home');
        $home->setOptions(
            array(
                'label'      => 'Home Address',
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
        $this->addElement($home);
        
        
        
        
         
        $office = new Zend_Form_Element_Text('office');
        $office->setOptions(
            array(
                'label'      => 'Office Address',
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
        $this->addElement($office);
        
        
        
        $shop = new Zend_Form_Element_Text('shop');
        $shop->setOptions(
            array(
                'label'      => 'Shop Address',
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
        $this->addElement($shop);
        
        
        
        
        
        $mobile1 = new Zend_Form_Element_Text('mobile1');
        $mobile1->setOptions(
            array(
                'label'      => 'Mobile no.',
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
        $this->addElement($mobile1);
        
        
        
        
        
        $mobile2 = new Zend_Form_Element_Text('mobile2');
        $mobile2->setOptions(
            array(
                'label'      => 'Alternate Contact No.',
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
        $this->addElement($mobile2);
        
        $matric_school_name = new Zend_Form_Element_Text('matric_school_name');
        $matric_school_name->setOptions(
            array(
                'label'      => 'Metric School Name *',
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
        $this->addElement($matric_school_name);
        
        
        
        
        
        $intermediate_school_name = new Zend_Form_Element_Text('intermediate_school_name');
        $intermediate_school_name->setOptions(
            array(
                'label'      => 'Intermediate School Name *',
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
        $this->addElement($intermediate_school_name);
        
        
        
        $graduation_degree = new Zend_Form_Element_Text('graduation_degree');
        $graduation_degree->setOptions(
            array(
                'label'      => 'Graduation Degree *',
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
        $this->addElement($graduation_degree);
        
        
        
        
        
        
        $graduation_college = new Zend_Form_Element_Text('graduation_college');
        $graduation_college->setOptions(
            array(
                'label'      => 'Graduation College *',
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
        $this->addElement($graduation_college);
        
        
        
        
        
        $p_graduation_degree = new Zend_Form_Element_Text('p_graduation_degree');
        $p_graduation_degree->setOptions(
            array(
                'label'      => 'Post Grad Degree',
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
        $this->addElement($p_graduation_degree);
        
        
        
        
        
        $p_graduation_college = new Zend_Form_Element_Text('p_graduation_college');
        $p_graduation_college->setOptions(
            array(
                'label'      => 'Post Grad College *',
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
        $this->addElement($p_graduation_college);
        
        
        
        
        $other_degree = new Zend_Form_Element_Text('other_degree');
        $other_degree->setOptions(
            array(
                'label'      => 'Other Degree',
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
        $this->addElement($other_degree);
        
        
        
        
         
        $other_college = new Zend_Form_Element_Text('other_college');
        $other_college->setOptions(
            array(
                'label'      => 'Other College',
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
        $this->addElement($other_college);
        
        
         $date_of_birth = new Zend_Form_Element_Text('date_of_birth');
        $date_of_birth->setOptions(
            array(
                'label'      => 'Date Of Birth *',
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
        $this->addElement($date_of_birth);
        
        
        
        
        
        $gender = new Zend_Form_Element_Select('gender');
        $gender->setOptions(
            array(
                'label'      => 'Gender *',
                'multioptions'    => array(
                            'Male' => 'Male',
                            'Female' => 'Female',
                            
                        ), 

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
        $this->addElement($gender);
        
        
        
        $Identification_type = new Zend_Form_Element_Select('Identification_type');
        $Identification_type->setOptions(
            array(
                'label'      => 'Identification Type *',
                'multioptions'    => array(
                            'Passport' => 'Passport',
                            'UID' => 'UID',
                            'Licence' => 'Licence',
                            
                        ),
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
        $this->addElement($Identification_type);
        
        
        $Identification_number = new Zend_Form_Element_Text('Identification_number');
        $Identification_number->setOptions(
            array(
                'label'      => 'Identification No. *',
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
        $this->addElement($Identification_number);
        
        
        
        
        $pan_number = new Zend_Form_Element_Text('pan_number');
        $pan_number->setOptions(
            array(
                'label'      => 'PAN *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(10, 10)),
                                ),
                'maxlength' => '10',
            )
        );
        $this->addElement($pan_number);
        
           $res_type = new Zend_Form_Element_Select('res_type');
        $res_type->setOptions(
            array(
                'label'      => 'Gender *',
                'multioptions'    => array(
                            'Permanent' => 'Permanent',
                            'Rented' => 'Rented',
                            
                        ), 

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
            )
        );
        $this->addElement($res_address1);
        
        
        
        $res_address2 = new Zend_Form_Element_Text('res_address2');
        $res_address2->setOptions(
            array(
                'label'      => 'Address Line 2 *',
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
        $this->addElement($res_address2);
        
        
      
        
        
        
        $res_city = new Zend_Form_Element_Text('res_city');
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
            )
        );
        $this->addElement($res_city);
        
        
        $res_taluka = new Zend_Form_Element_Text('res_taluka');
        $res_taluka->setOptions(
            array(
                'label'      => 'Taluka *',
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
        $this->addElement($res_taluka);
        
        
        
        
        $res_district = new Zend_Form_Element_Text('res_district');
        $res_district->setOptions(
            array(
                'label'      => 'District*',
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
        $this->addElement($res_district);
        
        
        
        
         $res_state = new Zend_Form_Element_Text('res_state');
        $res_state->setOptions(
            array(
                'label'      => 'State*',
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
        $this->addElement($res_state);
        
        
         $res_country = new Zend_Form_Element_Text('res_country');
        $res_country->setOptions(
            array(
                'label'      => 'Country*',
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
        $this->addElement($res_country);
        
        
        
         $res_pincode = new Zend_Form_Element_Text('res_pincode');
        $res_pincode->setOptions(
            array(
                'label'      => 'Pincode *',
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
        $this->addElement($res_pincode);
           $fund_account_type = new Zend_Form_Element_Select('fund_account_type');
        $fund_account_type->setOptions(
            array(
                'label'      => 'Fund Account Type *',
                'multioptions'    => array(
                            'By Agent' => 'By Agent',
                            'Principal distributor' => 'Principal distributor',
                            
                        ), 

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
        $this->addElement($fund_account_type);
        
        
        $bank_name = new Zend_Form_Element_Text('bank_name');
        $bank_name->setOptions(
            array(
                'label'      => 'Bank Name *',
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
        $this->addElement($bank_name);
        
        
        
        $bank_account_number = new Zend_Form_Element_Text('bank_account_number');
        $bank_account_number->setOptions(
            array(
                'label'      => 'Bank Account No. *',
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
        $this->addElement($bank_account_number);
        
        
      
        
        
        
        $bank_id = new Zend_Form_Element_Text('bank_id');
        $bank_id->setOptions(
            array(
                'label'      => 'Bank Id *',
               
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
        $this->addElement($bank_id);
        
        
        $bank_location = new Zend_Form_Element_Text('bank_location');
        $bank_location->setOptions(
            array(
                'label'      => 'Bank Location*',
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
        $this->addElement($bank_location);
        
        
        
        
        $bank_city = new Zend_Form_Element_Text('bank_city');
        $bank_city->setOptions(
            array(
                'label'      => 'Bank City*',
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
        $this->addElement($bank_city);
        
        
        
        
         $bank_ifsc_code = new Zend_Form_Element_Text('bank_ifsc_code');
        $bank_ifsc_code->setOptions(
            array(
                'label'      => 'IFSC Code*',
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
        $this->addElement($bank_ifsc_code);
        
        
         $branch_id = new Zend_Form_Element_Text('branch_id');
        $branch_id->setOptions(
            array(
                'label'      => 'Branch Id*',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', 'Digits', array('StringLength', false, array(5, 5)),
                                ),
                'maxlength' => '5',
            )
        );
        $this->addElement($branch_id);
        
        
        
         $bank_area = new Zend_Form_Element_Text('bank_area');
        $bank_area->setOptions(
            array(
                'label'      => 'Bank Area *',
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
        $this->addElement($bank_area);
        
       
        
        $bank_branch_id = new Zend_Form_Element_Text('bank_branch_id');
        $bank_branch_id->setOptions(
            array(
                'label'      => 'Bank branch ID *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty', 'Digits', array('StringLength', false, array(5, 5)),
                                ),
                'maxlength' => '5',
            )
        );
        $this->addElement($bank_branch_id);
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Agent →',
                'required'   => true,
            )
        );
        $this->addElement($submit);
    }
}