<?php
/**
 * Form for adding new product limit
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Mvc_ProductLimitForm extends App_Operation_Form
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
        
        $products = new Products();
        $productOptions = $products->getProducts();
        
            
       /* $bank_id = new Zend_Form_Element_Select('product_id');
        $bank_id->setOptions(
            array(
                'label'      => 'Product Name*',
                'multioptions'    => $productOptions, 
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
        $this->addElement($bank_id);
        */
        								

        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'Product Limit Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   
                                ),
               'maxlength' => '80',
               //'addRupeeSymbol' => true,
            )
        );
        $this->addElement($name);
        
        
        
        
        
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
                'style' => 'width:210px;'
            )
        );
        $this->addElement($currency);
       
   
         $name = new Zend_Form_Element_Text('limit_out_first_load');
        $name->setOptions(
            array(
                'label'      => 'Minimum Amount for First Load',
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
                 
        $name = new Zend_Form_Element_Text('limit_out_min_txn');
        $name->setOptions(
            array(
                'label'      => 'Minimum Reload Amount per Trxn',
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
                'label'      => 'Maximum Reload Amount per Trxn',
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
                'label'      => 'Max Amount per Day',
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
       
        
       
         
        
        $id = new Zend_Form_Element_Hidden('pname');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                   // new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
        $pid = new Zend_Form_Element_Hidden('pid');
        $pid->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($pid);
       
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Product Limit',
                'required'   => TRUE,
                'title'       => 'Save Product Limit',
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