<?php
/**
 * Remitter Search form
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Remit_Ratnakar_RemitterHoldTransactionForm extends App_Operation_Form
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
        
       
               
              
       
        
        $keyword = new Zend_Form_Element_Text('mobile');
        $keyword->setOptions(
            array(
                'label'      => 'Mobile *',
                'required'   => true,
                'filters'    => array(
                                    'StringTrim',
                                    'StripTags',
                                ),
                 'validators' => array(
                                    'NotEmpty','Digits', array('StringLength', false, array(10, 10)),
                                ),
                 'maxlength' => '10',
            )
        );
        $this->addElement($keyword);
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
                'label'      => 'Search Hold Transactions',
                'required'   => FALSE,
                'title'       => 'Search Hold Transactions',
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