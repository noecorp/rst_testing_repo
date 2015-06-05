<?php
/**
 * Form for adding new master fee in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

//class AgentMasterfeeForm extends App_Operation_Form
class CorporatelimitcreateForm extends App_Operation_Form
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
        
      
        
        
        
        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Agent Limit Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty' ,array('StringLength', false, array(4, 60)),
                                ),
                'maxlength' => '80',
            )
        );
        
        $this->addElement($name);
        $name->addValidator('Alpha', true, array('allowWhiteSpace' => true));
       
        
        
        
        
        
        $currency = new Currency();
        $currencyOptions = $currency->getAllCurrencyForDropDown();
        
        $currency = new Zend_Form_Element_Select('currency');
        $currency->setOptions(
            array(
                'label'      => 'Currency *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    
                                ),
                'multiOptions' => $currencyOptions,
            )
        );
        $this->addElement($currency);
        							

        $name = new Zend_Form_Element_Text('limit_out_min_txn');
        $name->setOptions(
            array(
                'label'      => 'Minimum Amount per Trxn ',
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
       
        
        
        $name = new Zend_Form_Element_Text('limit_out_max_txn');
        $name->setOptions(
            array(
                'label'      => 'Maximum Amount per Trxn ',
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
   
        
        $name = new Zend_Form_Element_Text('cnt_out_max_txn_daily');
        $name->setOptions(
            array(
                'label'      => 'Max no. of Txns per Day',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($name);
        
        $name = new Zend_Form_Element_Text('limit_out_max_daily');
        $name->setOptions(
            array(
                'label'      => 'Max Amount per Day ',
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
        
        $name = new Zend_Form_Element_Text('cnt_out_max_txn_monthly');
        $name->setOptions(
            array(
                'label'      => 'Max no. of Txns per Month',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($name);
        
         $name = new Zend_Form_Element_Text('limit_out_max_monthly');
        $name->setOptions(
            array(
                'label'      => 'Max Amount per Month',
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
        
        $name = new Zend_Form_Element_Text('cnt_out_max_txn_yearly');
        $name->setOptions(
            array(
                'label'      => 'Max no. of Txns per Year',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                     'Digits',
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($name);
        
        
        
       
        
        $name = new Zend_Form_Element_Text('limit_out_max_yearly');
        $name->setOptions(
            array(
                'label'      => 'Max Amount per Year',
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
       
        
       
       
         
       
        
         $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Corporate Limit',
                'required'   => TRUE,
                'title'       => 'Save Corporate Limit',
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