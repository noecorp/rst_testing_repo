<?php

class Corp_Boi_DisbursementLoadReport extends App_Operation_Form
{
    
    public function init()
    {   
        $this->_cancelLink = false;
        
        
        //$buckets =  Util::getBucket();
        $buckets = Zend_Registry::get("BOI_NSDC_DISBURSEMENT_BUCKETS");
        $buckets[''] = 'All';
        ksort($buckets);
        //return $globalBuckets;
        
        $batchname = $this->addElement('text', 'batch_name', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 100)),),
            'required'   => false,
            'label'      => 'File Name: ',
        ));
        
        $disbursement = $this->addElement('text', 'disbursement_number', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 100)),),
            'required'   => false,
            'label'      => 'Disbursement Number: ',
        ));
        
        $aadhar_no = $this->addElement('text', 'aadhar_no', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 100)),),
            'required'   => false,
            'label'      => 'Aadhar No: ',
        ));
        
        $account_number = $this->addElement('text', 'account_number', array(
            'filters'    => array('StringTrim', 'StringToLower'),
            'validators' => array(array('StringLength', false, array(1, 100)),),
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
