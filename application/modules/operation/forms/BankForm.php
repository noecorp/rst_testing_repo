<?php
/**
 * Form for adding new privileges in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

//class BankForm extends Zend_Form
class BankForm extends App_Operation_Form
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
        $bankList = new BanksIFSC();
        $bankListOptions = $bankList->getBank();
       
        

     $bankname = new Zend_Form_Element_Select('name');
        $bankname->setOptions(
            array(
                'label'      => 'Bank Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 100)),
                                ),
                'maxlength' => '100',
                'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
        
        $ifsc_code = new Zend_Form_Element_Select('ifsc_code');
        $ifsc_code->setOptions(
            array(
                'label'      => 'IFSC Code *',
               
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty'
                                ),
                'multioptions'    => array('' =>'Select IFSC Code'),
            )
        );
        $ifsc_code->setRegisterInArrayValidator(false);
        $this->addElement($ifsc_code);      
        
       
                
        
       /* $swift_code = new Zend_Form_Element_Text('swift_code');
        $swift_code->setOptions(
            array(
                'label'      => 'Swift Code ',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
            )
        );
        $this->addElement($swift_code);
        * */
        
        
        $branch_name = new Zend_Form_Element_Text('branch_name');
        $branch_name->setOptions(
            array(
                'label'      => 'Branch Name *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($branch_name);
            
        
        
        $city = new Zend_Form_Element_Text('city');
        $city->setOptions(
            array(
                'label'      => 'Branch City *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 50)),
                                ),
                'maxlength' => '50',
            )
        );
        $this->addElement($city);            
        
   
        
        
        $address = new Zend_Form_Element_Text('address');
        $address->setOptions(
            array(
                'label'      => 'Branch Address',
                'required'   => FALSE,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty', array('StringLength', false, array(4, 250)),
                                ),
                'maxlength' => '250',
            )
        );
        $this->addElement($address);
        
        
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
        
        $id = new Zend_Form_Element_Hidden('ifsc');
        $id->setOptions(
            array(
                'validators' => array(                    // either empty or numeric
                   
                ),
            )
        );
        $this->addElement($id);
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Bank',
                'required'   => FALSE,
                'title'       => 'Save Bank',
                'class'     => 'tangerine',
                //'style'     => 'float: left; clear: both; word-wrap: normal;'
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