<?php
/**
 * User login form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class AgentLimitsForm extends App_Operation_Form
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
        
        
        
        $bank = new Approveagent();
        $bankOptions = $bank->getBank();
        $commPlanOptions = $bank->getCommissionPlan();
        
        $feePlan = new FeePlan();
        $feePlanOptions = $feePlan->getFeePlanSelect();
        
        $agentlimit = new Agentlimit();
        $limitIdOptions = $agentlimit->getAgentlimits();
        
        $bank_id = new Zend_Form_Element_Select('bank_id');
        $bank_id->setOptions(
            array(
                'label'      => 'Bank Name *',
                'multioptions'    => $bankOptions, 
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
        
        $fee_id = new Zend_Form_Element_Select('product_id');
        $fee_id->setOptions(
            array(
                'label'      => 'Product Name *',
                'multioptions'    => array('' =>'Select Product'), 
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
        $fee_id->setRegisterInArrayValidator(false);
        $this->addElement($fee_id);
        
        $fee_id = new Zend_Form_Element_Select('product_limit_id');
        $fee_id->setOptions(
            array(
                'label'      => 'Product Limit *',
                'multioptions'    => array('' =>'Select Product Limit'), 
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
        $fee_id->setRegisterInArrayValidator(false);
        $this->addElement($fee_id);
        
        $fee_id = new Zend_Form_Element_Select('plan_commission_id');
        $fee_id->setOptions(
            array(
                'label'      => 'Commission Plan *',
                'multioptions'    => $commPlanOptions, 
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
        $fee_id->setRegisterInArrayValidator(false);
        $this->addElement($fee_id);
        
        $fee_id = new Zend_Form_Element_Select('plan_fee_id');
        $fee_id->setOptions(
            array(
                'label'      => 'Fee Plan *',
                'multioptions'    => $feePlanOptions, 
                'required'   => false,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
//                'validators' => array(
//                                    'NotEmpty',
//                                ),
            )
        );
        $fee_id->setRegisterInArrayValidator(false);
        $this->addElement($fee_id);
        
        $productId = new Zend_Form_Element_Select('agent_limit_id');
        $productId->setOptions(
            array(
                'label'      => 'Agent Limit *',
                'required'   => TRUE,
                'filters'    => array(
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'multiOptions' => $limitIdOptions,
            )
        );
        $this->addElement($productId);  
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('date_start',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            //'validators' =>  new Zend_Validate_Callback(array($new , 'currentDateValidation')),//array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'validators' =>  array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => true,
            'maxlength'  => 10,
            'label'      => 'Start Date: (e.g. dd-mm-yyyy) *',
            'style'     => 'width:200px;',)

        ));  
        
         $master = new Zend_Form_Element_Hidden('product');
        $master->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($master);
         
        $product = new Zend_Form_Element_Hidden('limit');
        $product->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($product);

        
         $product = new Zend_Form_Element_Hidden('id');
        $product->setOptions(
            array(
                'validators' => array(
                    // either empty or numeric
                    new Zend_Validate_Regex('/^\d*$/'),
                ),
            )
        );
        $this->addElement($product);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Assign Product',
                'required'   => FALSE,
                'title'       => 'Assign Product',
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