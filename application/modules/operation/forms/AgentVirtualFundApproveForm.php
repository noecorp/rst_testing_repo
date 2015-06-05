<?php

/**
 * Agent Virtual Limit Form
 * Here Operation User add virtual limit of agent 
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 *
 */
class AgentVirtualFundApproveForm extends App_Operation_Form {

    /**
     * This form does not have a cancel link
     * 
     * @var mixed
     * @access protected
     */
    protected $_cancelLink = TRUE;

    /**
     * Overrides init() in Zend_Form
     * 
     * @access public
     * @return void
     */
    public function init() {
        // init the parent
        parent::init();
        $params = $this->getAttrib('params');
        $this->_cancelLink = $params['cancelLink'];

        $this->addElement('hidden', 'agent_funding_id', array(
            'filters' => array('StringTrim'),
            'required' => FALSE,
            'value' => $params['agent_funding_id']
        ));


        $this->addElement('textarea', 'remarks', array(
            'filters' => array('StringTrim'),
            'required' => FALSE,
            'label' => 'Approve Remarks:',
            'rows' => 8,
            'cols' => 70
        ));

        $btn = new Zend_Form_Element_Hidden('approve_request');
        $btn->setOptions(
                array(
                    'value' => '1'
                )
        );
        $this->addElement($btn);

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
                array(
                    'label' => 'Yes, Approve Fund Request',
                    'required' => false,
                    'ignore' => true,
                    'title' => 'Yes, Approve',
                    'class' => 'tangerine'
                )
        );
        $this->addElement($submit);

        $this->setElementDecorators(array(
            'viewHelper',
            'Errors',
            array(array('data' => 'HtmlTag'), array('tag' => 'dd', 'class' => 'form-field-column edit')),
            array('Label', array('tag' => 'dt', 'class' => 'form-name-column')),
        ));
        $this->setDecorators(array(
            'FormElements',
            array(array('Value' => 'HtmlTag'), array('tag' => 'dl', 'class' => 'innerboxForm form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }

}
