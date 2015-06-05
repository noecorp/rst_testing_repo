<?php
/**
 * Agent Fund Transfer Form
 *
 */

class CorporateFundTransferForm extends App_Corporate_Form
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
        
        $amount = new Zend_Form_Element_Text('amount');
        $amount->setOptions(
            array(
                'label'      => 'Amount *',
                //'multioptions'    => array('' =>'Select Product'), 
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                 'validators' => array('Digits', array('StringLength', false, array(2, 8)),),

            )
        );
        //$fee_id->setMultiOptions($productArr);
        $this->addElement($amount);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Transfer Fund',
                'required'   => FALSE,
                'title'       => 'Save Agent',
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