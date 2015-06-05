<?php
/**
 * Form for adding new privileges in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

//class TransactiontypeForm extends Zend_Form
class TransactiontypeForm extends App_Operation_Form
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
        
     $typecode = new Zend_Form_Element_Text('typecode');
         $typecode->setOptions(
            array(
                'label'      => 'Type Code *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 4)),
                                ),
                'maxlength'  => '4',
                
               
            )
        );
        $this->addElement($typecode);
        
        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 100)),
                                ),
                'maxlength'  => '50',
            )
        );
        $this->addElement($name);
        
        
        $comm = new Zend_Form_Element_Radio('is_comm');
        $comm->setLabel('Is Commission *:')
            ->addMultiOptions(array(
                    'no' => FLAG_NO,
                    'yes' => FLAG_YES
                        ))
            ->setSeparator('    ')
            ->setValue(FLAG_NO);
         $this->addElement($comm);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'    => 'Save Transaction Type',
                'required' => false,
                'ignore'   => true,
                'title'    => 'Save Transaction Type',
                'class'    => 'tangerine',
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
    
    /**
     * Overrides populate() in App_Form
     * 
     * @param mixed $data 
     * @access public
     * @return void
     */
    
}