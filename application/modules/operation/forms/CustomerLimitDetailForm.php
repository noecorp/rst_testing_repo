<?php
/**
 * Form for editing master purse in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class CustomerLimitDetailForm extends App_Operation_Form
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
        
        
        $balance = new Zend_Form_Element_Text('max_balance');
        $balance->setOptions(
            array(
                'label'      => 'Max Balance',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($balance);
        
       
        
        $load_min = new Zend_Form_Element_Text('load_min');
        $load_min->setOptions(
            array(
                'label'      => 'Minimum Value per Load',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($load_min);
        
        $load_max = new Zend_Form_Element_Text('load_max');
        $load_max->setOptions(
            array(
                'label'      => 'Maximum Value per Load',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($load_max);
   
       	 	
        
        $name = new Zend_Form_Element_Text('load_max_val_daily');
        $name->setOptions(
            array(
                'label'      => 'Max Load Amount per Day',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);
         
       
        
        $name = new Zend_Form_Element_Text('load_max_val_monthly');
        $name->setOptions(
            array(
                'label'      => 'Max Load Amount per Month',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);
     
        $name = new Zend_Form_Element_Text('load_max_val_yearly');
        $name->setOptions(
            array(
                'label'      => 'Max Load Amount per Year',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);

       
        
        
        $min = new Zend_Form_Element_Text('txn_min');
        $min->setOptions(
            array(
                'label'      => 'Minimum Value of Txn',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($min);
        
        $max = new Zend_Form_Element_Text('txn_max');
        $max->setOptions(
            array(
                'label'      => 'Maximum Value of Txn',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($max);
   
 	
        $name = new Zend_Form_Element_Text('txn_max_val_daily');
        $name->setOptions(
            array(
                'label'      => 'Max Txn Amount per Day',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);
         
        $name = new Zend_Form_Element_Text('txn_max_val_monthly');
        $name->setOptions(
            array(
                'label'      => 'Max Txn Amount per Month',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);
     
        
        
        
        
       
        
        $name = new Zend_Form_Element_Text('txn_max_val_yearly');
        $name->setOptions(
            array(
                'label'      => 'Max Txn Amount per Year',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
                 'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);
        
//        $date = new Zend_Form_Element_Text('date_start');
//        $date->setOptions(
//            array(
//                'label'      => 'Date Start (Changes will be implimented from tomorrow)',
//                'required'   => TRUE,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//              
//                'maxlength' => '10',
//            )
//        );
//        $this->addElement($date);
//        $date->setAttrib('readonly', true);
//        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Customer Limit',
                'required'   => TRUE,
                'title'       => 'Save Customer Limit',
                'class'     => 'tangerine',
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