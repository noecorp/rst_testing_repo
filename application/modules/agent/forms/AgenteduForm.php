<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgenteduForm extends App_Agent_Form
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
        
        $education_level = new Zend_Form_Element_Select('education_level');
        $education_level->setOptions(
            array(
                'label'      => 'Education Level *',
                'required'   => true,
                'multioptions'    => Util::getEducationType(),
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($education_level);
       
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
                                    'NotEmpty', array('StringLength', false, array(5, 30)),
                                ),
                'maxlength' => '30',
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
                                    'NotEmpty', array('StringLength', false, array(5, 30)),
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($intermediate_school_name);
        
        
        
        $graduation_degree = new Zend_Form_Element_Text('graduation_degree');
        $graduation_degree->setOptions(
            array(
                'label'      => 'Graduation Degree',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 40)),
                                ),
                'maxlength' => '40',
            )
        );
        $this->addElement($graduation_degree);
        
        
        
        
        
        
        $graduation_college = new Zend_Form_Element_Text('graduation_college');
        $graduation_college->setOptions(
            array(
                'label'      => 'Graduation College',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 40)),
                                ),
                'maxlength' => '40',
            )
        );
        $this->addElement($graduation_college);
        
        
        
        
        
        $p_graduation_degree = new Zend_Form_Element_Text('p_graduation_degree');
        $p_graduation_degree->setOptions(
            array(
                'label'      => 'Post Grad Degree',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 40)),
                                ),
                'maxlength' => '40',
                
            )
        );
        $this->addElement($p_graduation_degree);
        
        
        
        
        
        $p_graduation_college = new Zend_Form_Element_Text('p_graduation_college');
        $p_graduation_college->setOptions(
            array(
                'label'      => 'Post Grad College',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 40)),
                                ),
                'maxlength' => '40',
            )
        );
        $this->addElement($p_graduation_college);
        
        
        
        
        $other_degree = new Zend_Form_Element_Text('other_degree');
        $other_degree->setOptions(
            array(
                'label'      => 'Other Degree',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 40)),
                                ),
                'maxlength' => '40',
            )
        );
        $this->addElement($other_degree);
        
        
        
        
         
        $other_college = new Zend_Form_Element_Text('other_college');
        $other_college->setOptions(
            array(
                'label'      => 'Other College',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(3, 40)),
                                ),
                'maxlength' => '40',
            )
        );
        $this->addElement($other_college);
        
        
        
        
        
        
         
         $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Education Details',
                'required'   => FALSE,
                'title'       => 'Save Education Details',
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