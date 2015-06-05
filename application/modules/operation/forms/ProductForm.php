<?php
/**
 * Form for adding new products in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class ProductForm extends App_Operation_Form
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
        
        $product = new Products();
        $bankIdOptions = $product->getBank();
        
        
        $currency = new Currency();
        $currencyOptions = $currency->getAllCurrencyForDropDown();
        

        $bankId = new Zend_Form_Element_Select('bank_id');
        $bankId->setOptions(
            array(
                'label'      => 'Bank *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'multiOptions' => $bankIdOptions,
            )
        );
        $this->addElement($bankId);
        
        $programType = new Zend_Form_Element_Select('program_type');
        $programType->setOptions(
            array(
                'label'      => 'Program Type *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'multiOptions' => Util::getProgramType(),
            )
        );
        $this->addElement($programType);
        
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
                                    'NotEmpty',
                                ),
                'style' => 'width:210px;',
                'multiOptions' => $currencyOptions,
            )
        );
        $this->addElement($currency);
                
        
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
                                    'NotEmpty', array('StringLength', false, array(4, 60)),
                                ),
                 'maxlength' => '80',
            )
        );
        $this->addElement($name);

        $description = new Zend_Form_Element_Text('description');
        $description->setOptions(
            array(
                'label'      => 'Description *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 90)),
                                ),
                'maxlength' => '90',
            )
        );
        $this->addElement($description);
        
        $id = new Zend_Form_Element_Hidden('id');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
         $id = new Zend_Form_Element_Hidden('bid');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        
           $id = new Zend_Form_Element_Hidden('urlBid');
        $id->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($id);
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Product',
                'required'   => FALSE,
                'title'       => 'Save Product',
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