<?php
/**
 * Form for adding new privileges in the application
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

//class CommissionitemForm extends Zend_Form
class CommissionitemForm extends App_Operation_Form
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
        
        $commissionplanModel = new CommissionPlan();
        $typecodeIdOptions = $commissionplanModel->getTypecode();
        
        $ValidateRange =  new Zend_Validate_Between(0, MAX_FLOAT_LIMIT);
        $ValidateRangePct =  new Zend_Validate_Between(0, MAX_FLOAT_LIMIT_PCT);

        $typecode = new Zend_Form_Element_Select('typecode');
        $typecode->setOptions(
            array(
                'label'      => 'Transaction Code *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'multiOptions' => $typecodeIdOptions,
            )
        );
        $this->addElement($typecode);  
        
        $txn_flat = new Zend_Form_Element_Text('txn_flat');
        $txn_flat->setOptions(
            array(
                'label'      => 'Fixed Amount per Trxn',
                'required'   => FALSE,
                'filters'    => array(
                                    'StripTags',
                                ),
                 'validators' => array(
                                    'Float'
                                ),
                 'maxlength' => '7',
                 'addRupeeSymbol' => true,
                
            )
        );
         $txn_flat->addValidator($ValidateRange);
         $this->addElement($txn_flat);
         
        $txn_pcnt = new Zend_Form_Element_Text('txn_pcnt');
        $txn_pcnt->setOptions(
            array(
                'label'      => '% of Amount',
                'required'   => FALSE,
                'filters'    => array(
                                    'StripTags',
                                ),
                 'validators' => array(
                                    'Float'
                                ),
                 'maxlength' => '5',
                
            )
        );
         $txn_pcnt->addValidator($ValidateRangePct);
         $this->addElement($txn_pcnt);
        
        $minLimit = new Zend_Form_Element_Text('txn_min');
        $minLimit->setOptions(
            array(
                'label'      => 'Minimum Amount per Trxn',
                'required'   => FALSE,
                'filters'    => array(
                                    'StripTags',
                                ),
                 'validators' => array(
                                    'Float'
                                ),
                 'maxlength' => '7',
                 'addRupeeSymbol' => true,
            )
        );
        $minLimit->addValidator($ValidateRange);
        $this->addElement($minLimit);        
        
        
         
        
        $maxLimit = new Zend_Form_Element_Text('txn_max');
        $maxLimit->setOptions(
            array(
                'label'      => 'Maximum Amount per Trxn',
                'required'   => FALSE,
                'filters'    => array(
                                    'StripTags',
                                ),
                 'validators' => array(
                                    'Float'
                                ),
                 'maxlength' => '7',
                 'addRupeeSymbol' => true,
                
            )
        );
        
        
        $maxLimit->addValidator($ValidateRange);
        $this->addElement($maxLimit);    
        
       
        $code = new Zend_Form_Element_Hidden('code');
        $code->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    //new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($code);
    
        
        
        $cid = new Zend_Form_Element_Hidden('cid');
        $cid->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($cid);
        
      
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Save Commission Plan',
                'required'   => TRUE,
                'title'       => 'Save Commission Plan',
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