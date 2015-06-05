<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class EditAgentForm extends App_Operation_Form
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
                                    'NotEmpty','EmailAddress',array('StringLength', false, array(9, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($email);
        
        $secemail = new Zend_Form_Element_Text('auth_email');
        $secemail->setOptions(
            array(
                'label'      => 'Secondary Email',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','EmailAddress'
                                ),
            )
        );
        $this->addElement($secemail);
        
        
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
        
       /* 
        $office = new Zend_Form_Element_Text('office');
        $office->setOptions(
            array(
                'label'      => 'Office Address ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 80)),
                                ),
                'maxlength' => '80',
                
            )
        );
        $this->addElement($office);
        
        
        
        $shop = new Zend_Form_Element_Text('shop');
        $shop->setOptions(
            array(
                'label'      => 'Shop Address',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(5, 80)),
                                ),
                'maxlength' => '80',
            )
        );
        $this->addElement($shop);
        
        
        */
        
        
        $mobile1 = new Zend_Form_Element_Text('mobile1');
        $mobile1->setOptions(
            array(
                'label'      => 'Mobile no. *',
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
        $mobile1->setAttrib('readonly', true);
        
        $stdlist = new CityList();
        $stdlistoptions = $stdlist->getSTDcode();
        
        $mobile2 = new Zend_Form_Element_Select('std');
        $mobile2->setOptions(
            array(
                'label'      => 'Std Code ',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim','Digits','StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                  'multioptions'    =>  $stdlistoptions,         
            )
        );
        $mobile2->setRegisterInArrayValidator(false);
        $this->addElement($mobile2);
        
        
        $mobile2 = new Zend_Form_Element_Text('mobile2');
        $mobile2->setOptions(
            array(
                'label'      => 'Alternate Contact No.',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(6, 8)),
                                ),
                'maxlength' => '8',
            )
        );
        $this->addElement($mobile2);
        
        $bcagent = new Zend_Form_Element_Text('bcagent');
        $bcagent->setOptions(
            array(
                'label'      => 'BC Agent',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'maxlength' => '15'
            )
        );
        $this->addElement($bcagent);
        
         $afn = new Zend_Form_Element_Text('institution_name');
        $afn->setOptions(
            array(
                'label'      => 'Institution Name (Applicable if an entity other than an individual is applying)',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(3, 50)),
                                ),
                 'maxlength' => '45',
            )
        );
        $this->addElement($afn);
          
        $centre_id = new Zend_Form_Element_Text('centre_id');
        $centre_id->setOptions(
            array(
                'label'      => 'Centre ID',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(1, 30)),
                                ),
                 'maxlength' => '30',
            )
        );
        $this->addElement($centre_id);
        
        $terminal_id_1 = new Zend_Form_Element_Text('terminal_id_tid_1');
        $terminal_id_1->setOptions(
            array(
                'label'      => 'Terminal ID (TID) 1',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(1, 30)),
                                ),
                 'maxlength' => '30',
            )
        );
        $this->addElement($terminal_id_1);
        
        $terminal_id_2 = new Zend_Form_Element_Text('terminal_id_tid_2');
        $terminal_id_2->setOptions(
            array(
                'label'      => 'Terminal ID (TID) 2',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(1, 30)),
                                ),
                 'maxlength' => '30',
            )
        );
        $this->addElement($terminal_id_2);
        
        $terminal_id_3 = new Zend_Form_Element_Text('terminal_id_tid_3');
        $terminal_id_3->setOptions(
            array(
                'label'      => 'Terminal ID (TID) 3',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(1, 30)),
                                ),
                 'maxlength' => '30',
            )
        );
        $this->addElement($terminal_id_3);
        
        $status = new Zend_Form_Element_Hidden('status');
        $status->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    //new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($status);
        
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
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
         $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Agent',
                'required'   => FALSE,
                'title'       => 'Save Agent',
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