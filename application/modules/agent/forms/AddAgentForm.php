<?php

class AddAgentForm extends Zend_Form
{
  
    public function  init()
    {
        $afn = $this->addElement('text', 'afn', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'AFN: *',
            'style'     => 'width:200px;',
        ));
        
        $email = $this->addElement('text', 'email', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('EmailAddress'),
            'required'   => true,
            'label'      => 'Email: *',
            'style'     => 'width:200px;',
        ));
        $first_name = $this->addElement('text', 'first_name', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'First name: *',
            'style'     => 'width:200px;',
        ));
        $middle_name = $this->addElement('text', 'middle_name', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'Middle Name: *',
            'style'     => 'width:200px;',
        ));
         $last_name = $this->addElement('text', 'last_name', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'Last Name: *',
            'style'     => 'width:200px;',
        ));
          
          $home = $this->addElement('text', 'home', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'Home Address: *',
            'style'     => 'width:200px;',
        ));
          $office = $this->addElement('text', 'office', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'Office Address: *',
            'style'     => 'width:200px;',
        ));
          $shop = $this->addElement('text', 'shop', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(5, 100)),),
            'required'   => true,
            'label'      => 'Shop Address: *',
            'style'     => 'width:200px;',
        ));
       /* $password = $this->addElement('password', 'password', array(
            'filters'    => array('StringTrim'),
            'validators' => array('Alnum',array('StringLength', false, array(4, 20)),),
            'required'   => true,
            'label'      => 'Password: *',
            'style'     => 'width:200px;',
        ));
        $status = $this->addElement('text', 'status', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 100)),),
            'required'   => true,
            'label'      => 'Status:',
            'style'     => 'width:200px;',
        ));
        
        $activation_code = $this->addElement('text', 'activation_code', array(
            'filters'    => array('StringTrim', 'StringToLower'),
             'validators' => array('NotEmpty', array('StringLength', false, array(1, 10)),),
            'required'   => true,
            'label'      => 'Activation Code: *',
            'style'     => 'width:200px;',
        ));
        
        $agent_code = $this->addElement('text', 'agent_code', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'label'      => 'Agent Code:',
            'style'     => 'width:400px;height:100px;',

        ));
        
         $principle_distributor_id = $this->addElement('text', 'principle_distributor_id', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 100)),),
            'required'   => true,
            'label'      => 'Principle Distributor Id:',
            'style'     => 'width:200px;',
        ));*/
         $mobile1 = $this->addElement('text', 'mobile1', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 100)),),
            'required'   => true,
            'label'      => 'Mobile No.:',
            'style'     => 'width:200px;',
        ));
          $mobile2 = $this->addElement('text', 'mobile2', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 100)),),
            'required'   => true,
            'label'      => 'Alternate Mobile no.:',
            'style'     => 'width:200px;',
        ));
        /*   $date_created = $this->addElement('text', 'date_created', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array('NotEmpty', array('StringLength', false, array(1, 100)),),
            'required'   => true,
            'label'      => 'Date Created:',
            'style'     => 'width:200px;',
        ));*/
          
        $addagent = $this->addElement('submit', 'addagent', array(
            'required' => false,
            'ignore'   => true,
            'label'    => 'Add Agent',
        ));


        // We want to display a 'failed authentication' message if necessary;
        // we'll do that with the form 'description', so we need to add that
        // decorator.
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
}
?>
