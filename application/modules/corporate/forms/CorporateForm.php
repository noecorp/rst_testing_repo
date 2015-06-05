<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class CorporateForm extends App_Corporate_Form
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
       
        //echo "dsfsdf"; exit;
        //$username = new Zend_Form_Element_Text('username');
        //$username->setOptions(
        //    array(
        //        'label'      => 'UserName *',
        //        'required'   => true,
        //        'filters'    => array(
        //                            'StringTrim',
        //                            'StripTags',
        //                        ),
        //        'validators' => array(
        //                            'NotEmpty',array('StringLength', false, array(2, 35)),
        //                        ),
        //          'maxlength' => '35',
        //    )
        //);
        //$this->addElement($username);
        //$username->addValidator('Alnum', true, array('allowWhiteSpace' => false));
        
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
        
        
        $user = Zend_Auth::getInstance()->getIdentity();
        $agentModel = new AgentUser();
        if(isset($user->id) && $agentModel->isSuperAgent($user->id)) {
            $parent_id = new Zend_Form_Element_Hidden('parent_agent_id');
            $parent_id->setValue($user->id);
            $this->addElement($parent_id);
        }              

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