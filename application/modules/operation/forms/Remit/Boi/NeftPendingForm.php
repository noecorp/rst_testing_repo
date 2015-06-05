<?php
/**
 * Form for editing groups per user
 *
 * @category backoffice
 * @package backoffice_forms
 * @copyright company
 */

class Remit_Boi_NeftPendingForm extends App_Operation_Form
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
        
        $remitRequestModel = new Remit_Remittancerequest();
        $remitRequests = $remitRequestModel->getPendingRemitRequests();
        //echo "<pre>";print_r($remitRequests);
        $displayRequests = array();
        foreach ($remitRequests as $remitRequest) {
            
           
                $checkbox = new Zend_Form_Element_Checkbox('reqId_' . $remitRequest['rmid']);
                
                $checkbox->setOptions(
                    array(
                        'label' =>  $remitRequest['rem_name'] ,
                    )
                );
                $this->addElement($checkbox);
                $displayRequests[] = $checkbox->getName();
            

           
        }
         
         
        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setOptions(
            array(
                'label'      => 'Process Instructions',
                'required'   => FALSE,
                'title'       => 'Process Instructions',
                'class'     => 'tangerine',
            )
        );
        $this->addElement($submit);
        
        $this->setElementDecorators(array(
                    'viewHelper',
                    'Errors',
                    array(array('data'=>'HtmlTag'),array('tag'=>'dd','class'=>'form-field-column edit')),
                    array('Label',array('tag'=>'dt','class'=>'form-name-column-wide')),
                   
                   
        ));
               
        $this->setDecorators(array(
            'FormElements',
            array(array('Value'=>'HtmlTag'), array('tag'=>'dl','class'=>'innerbox form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }
    
  
}