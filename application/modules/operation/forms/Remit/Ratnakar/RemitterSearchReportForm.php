<?php
/**
 * Remitter Search form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Remit_Ratnakar_RemitterSearchReportForm extends App_Operation_Form
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
        
       
               
        
        $name = new Zend_Form_Element_Text('name');
        $name->setOptions(
            array(
                'label'      => 'First Name',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
                'style'     => 'width:200px;',
            )
        );
        $this->addElement($name);
       
        
        $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('from_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'From: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));  
        
         $this->addElement(new ZendX_JQuery_Form_Element_DatePicker('to_date',
            array('jQueryParams' => array('dateFormat' => 'dd-mm-yy'),
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty', array('StringLength', false, array(10, 20)),),
            'required'   => false,
            'label'      => 'To: (e.g. dd-mm-yyyy) ',
            'maxlength'  => '20',
            'style'     => 'width:200px;',)

        ));
        
        $status = new Zend_Form_Element_Select('status');
        $status->setOptions(
            array(
                'label'      => 'Status',
                'multioptions'    => Util::getRemittaceResponseStatus(),
                            
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',
                                ),
                'style'     => 'width:200px;',
            )
        );
        $this->addElement($status);
        
        
        $utr = new Zend_Form_Element_Text('utr');
        $utr->setOptions(
            array(
                'label'      => 'UTR Number',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
                'style'     => 'width:200px;',
            )
        );
        $this->addElement($utr);
        
        $txn_code = new Zend_Form_Element_Text('txn_code');
        $txn_code->setOptions(
            array(
                'label'      => 'Cust Ref no',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
                'style'     => 'width:200px;',
            )
        );
        $this->addElement($txn_code);
        
        $mobile = new Zend_Form_Element_Text('mobile');
        $mobile->setOptions(
            array(
                'label'      => 'Mobile Number',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 11)),
                                ),
                  'maxlength' => '15',
                'style'     => 'width:200px;',
            )
        );
        $this->addElement($mobile);
        
        
        $bank_account_number = new Zend_Form_Element_Text('bank_account_number');
        $bank_account_number->setOptions(
            array(
                'label'      => 'Beneficiary Account Number',
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                'validators' => array(
                                    'NotEmpty',array('StringLength', false, array(2, 35)),
                                ),
                  'maxlength' => '35',
                'style'     => 'width:200px;',
            )
        );
        $this->addElement($bank_account_number);
        
        
        
        $btn = new Zend_Form_Element_Hidden('sub');
        $btn->setOptions(
            array(
                'value' => '1'
            )
        );
        $this->addElement($btn);
        
        
        
        
        
        
        
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Remittance Report',
                'required'   => FALSE,
                'title'       => 'Remittance Report',
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