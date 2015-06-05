<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentbankForm extends App_Agent_Form
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
        
        $bankList = new BanksIFSC();
        $bankListOptions = $bankList->getBank();
        
//          
//        $fund_account_type = new Zend_Form_Element_Select('fund_account_type');
//        $fund_account_type->setOptions(
//            array(
//                'label'      => 'Fund Account Type *',
//                'multioptions'    => Util::getFundAccountType(),
//
//                'required'   => true,
//                'filters'    => array(
//                                    'StringTrim',
//                                    'StripTags',
//                                ),
//                'validators' => array(
//                                    'NotEmpty',
//                                ),
//            )
//        );
//        $this->addElement($fund_account_type);
        
        
       $bankname = new Zend_Form_Element_Select('bank_name');
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
                'style' => 'width:210px;',
                'maxlength' => '100',
                 'multioptions'    => $bankListOptions,         
            )
        );
        $this->addElement($bankname);
        
        $ifsc_code = new Zend_Form_Element_Select('bank_ifsc_code');
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
                'style' => 'width:210px;',
            )
        );
        $ifsc_code->setRegisterInArrayValidator(false);
        $this->addElement($ifsc_code);      
        
        
        
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
                                    'NotEmpty','Alnum',
                                ),
                 'maxlength' => '35',
            )
        );
        $this->addElement($bank_account_number);
        
        
      
        
        
        
      /*  $bank_id = new Zend_Form_Element_Text('bank_id');
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
        */
        $bank_area = new Zend_Form_Element_Text('bank_area');
        $bank_area->setOptions(
            array(
                'label'      => 'Bank Area',
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '250',
            )
        );
        $this->addElement($bank_area);
        
        
        $bank_location = new Zend_Form_Element_Text('bank_location');
        $bank_location->setOptions(
            array(
                'label'      => 'Branch Name *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'maxlength' => '100',
            )
        );
        $this->addElement($bank_location);
        
        
        
        
        $bank_city = new Zend_Form_Element_Text('bank_city');
        $bank_city->setOptions(
            array(
                'label'      => 'Branch City *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                   'NotEmpty',
                                ),
                'maxlength' => '30',
            )
        );
        $this->addElement($bank_city);
        
        
        
        
       
        
        
        $branch_id = new Zend_Form_Element_Text('branch_id');
        $branch_id->setOptions(
            array(
                'label'      => 'Linked Branch ID',
                'required'   => FALSE,
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
                'label'      => 'Save Bank Details',
                'required'   => FALSE,
                'title'       => 'Save Bank Details',
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