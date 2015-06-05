<?php

class Corp_Boi_DisbursementUpdateStatus extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        
        $buckets =  Util::getBucket();
        $paymentStatusArr =  Util::getPaymentStatus();
        
        $disbursement = $this->addElement('text', 'disbursement_number', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 100)),),
            'required'   => false,
            'label'      => 'Disbursement Number: ',
        ));
        
        $aadhar_no = $this->addElement('text', 'aadhar_no', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 200)),),
            'required'   => false,
            'label'      => 'Aadhar No: ',
        ));
        
        $account_number = $this->addElement('text', 'account_number', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 200)),),
            'required'   => false,
            'label'      => 'Account Number: ',
        ));
        
        $bucket = $this->addElement('Multiselect', 'bucket', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            //'validators' => array(array('StringLength', false, array(1, 100)),),
            'required'   => false,
            'label'      => 'Bucket: ',
            'multioptions'    =>  $buckets,
        ));
        
        $payment_status = $this->addElement('select', 'payment_status', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'required'   => false,
            'label'      => 'Status: ',
            'multioptions'    =>  $paymentStatusArr,
        ));

        $noofrecords = $this->addElement('select', 'noofrecords', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'required'   => false,
            'label'      => 'No of Records: ',
            'multioptions'    => Util::getRecordsPerPage('high'),
        ));
                
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Submit',
                'required' => false,
                'ignore'   => true,
                'title'       => 'Submit',
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
?>
