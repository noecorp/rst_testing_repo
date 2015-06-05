<?php
/**
 * Model that manages the products
 *
 * @package Operation_Models
 * @copyright transerv
 */

class AgentImport extends App_Model
{
    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    protected $_primary = 'id';
    
    /**
     * Holds the table's name
     *
     * @var string
     * @access protected
     */
    protected $_name = DbTable::TABLE_AGENT_IMPORT;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Privilege';
    
    /**
     * Define the relationship with another tables
     *
     * @var array
     */
    
    //insert data array in agent_import table  //from csv
    public function insertAgentImport($dataArr, $batchName) {
        $user = Zend_Auth::getInstance()->getIdentity();
        
        $data = array(
            'application_form_no' => '',
            'distributor_code' => $dataArr[0],
            'title' => $dataArr[1],
            'first_name' => $dataArr[2],
            'middle_name' => $dataArr[3],
            'last_name' => $dataArr[4],
            'email' => $dataArr[5],
            'mobile' => $dataArr[6],
            'mobile2' => $dataArr[7],
            'institution_name' => $dataArr[8],
            'centre_id' => $dataArr[9],
            'terminal_id_1' => $dataArr[10],
            'terminal_id_2' => $dataArr[11],
            'terminal_id_3' => $dataArr[12],
            'education_level' => $dataArr[13],
            'matric_school_name' => $dataArr[14],
            'intermediate_school_name' => $dataArr[15],
            'graduation_degree' => $dataArr[16],
            'graduation_college' => $dataArr[17],
            'post_graduation_degree' => $dataArr[18],
            'post_graduation_college' => $dataArr[19],
            'other_degree' => $dataArr[20],
            'other_college' => $dataArr[21],
            'date_of_birth' => $dataArr[22],
            'gender' => $dataArr[23],
            'Identification_type' => $dataArr[24],
            'Identification_number' => $dataArr[25],
            'passport_expiry' => empty($dataArr[26]) ? '' : $dataArr[26],
            'address_proof_type' => $dataArr[27],
            'address_proof_number' => $dataArr[28],
            'pan_number' => $dataArr[29],
            'establishment_name' => $dataArr[30],
            'establishment_address1' => $dataArr[31],
            'establishment_address2' => $dataArr[32],
            'establishment_city' => $dataArr[33],
            'establishment_taluka' => $dataArr[34],
            'establishment_district' => $dataArr[35],
            'establishment_state' => $dataArr[36],
            'establishment_country' => $dataArr[37],
            'establishment_pincode' => $dataArr[38],
            'residence_type' => $dataArr[39],
            'residence_address1' => $dataArr[40],
            'residence_address2' => $dataArr[41],
            'residence_city' => $dataArr[42],
            'residence_taluka' => $dataArr[43],
            'residence_district' => $dataArr[44],
            'residence_state' => $dataArr[45],
            'residence_country' => $dataArr[46],
            'residence_pincode' => $dataArr[47],
            'bank_name' => $dataArr[48],
            'bank_account_number' => $dataArr[49],
            'bank_location' => $dataArr[50],
            'bank_city' => $dataArr[51],
            'bank_ifsc_code' => $dataArr[52],
            'Linked_branch_id' => $dataArr[53],
            'bank_area' => empty($dataArr[54]) ? '' : $dataArr[54],
            'import_status' => $dataArr['import_status'],
            'file_id' => $dataArr['file_id'],
            'failed_message' => $dataArr['failed_message']
        
        );
        
        $this->_db->insert(DbTable::TABLE_AGENT_IMPORT, $data);
        return TRUE;
    }
    
    //select details from agent_import with status pending,duplicate,temp
    public function showPendingAgentDetails($fileId, $page = 1, $paginate = NULL, $force = FALSE) {
        $select = $this->select();
        $select->from(DbTable::TABLE_AGENT_IMPORT);
        $select->where("import_status IN ('".STATUS_TEMP."', '".STATUS_DUPLICATE."', '".STATUS_PENDING."', '".STATUS_FAILED."')");
        $select->where('file_id = ?', $fileId);
        $select->order('id ASC');
        return $this->fetchAll($select);
        //return $this->_paginate($select, $page, $paginate);
    }
    
    //bulk update of status in agent_import on submit
    public function bulkUpdateAgentImport($idArr, $status = STATUS_PENDING) {
     
        if (empty($idArr))
            throw new Exception('Data missing for agent update');
        
        try {
                // Foreach selected id value
                foreach ($idArr as $id)
                {
                    $this->_db->beginTransaction();
                    $updateArr = array('import_status' => STATUS_PENDING);
                    $this->_db->update(DbTable::TABLE_AGENT_IMPORT, $updateArr, "id= $id");
                    $this->_db->commit();
                }// END of foreach loop
        } catch (Exception $e) {
            
            //echo "<pre>";print_r($e); exit;
            // If any of the queries failed and threw an exception,
            // we want to roll back the whole transaction, reversing
            // changes made in the transaction, even those that succeeded.
            // Thus all changes are committed together, or none are.
            App_Logger::log($e->getMessage(), Zend_Log::ERR);
            $this->_db->rollBack();
            //throw new Exception ("Transaction not completed due to system failure");
            throw new Exception($e->getMessage());
        }
        return TRUE;
    }
    
    //select details from agent_import
    public function getagentimportreport($data) {
        
        $file_name = isset($data['file_name']) ? $data['file_name'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $mobile = isset($data['mobile']) ? $data['mobile'] : '';
        $distributor_code = isset($data['distributor_code']) ? $data['distributor_code'] : '';
        $import_status = isset($data['import_status']) ? $data['import_status'] : '';
        $from_date = isset($data['from_date']) ? $data['from_date'] : '';
        $to_date = isset($data['to_date']) ? $data['to_date'] : '';
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(DbTable::TABLE_AGENT_IMPORT . ' as ai');
        $select->join(DbTable::TABLE_FILES . ' as fi', "ai.file_id = fi.id");
        
        if($file_name != ''){
            $select->where("fi.file_name =?" , $file_name);
        }
        if($email != ''){
            $select->where("ai.email =?" , $email);
        }
        if($mobile != ''){
            $select->where("ai.mobile =?" , $mobile);
        }
        if($distributor_code != ''){
            $select->where("ai.distributor_code =?" , $distributor_code);
        }
        if($import_status != ''){
            $select->where("ai.import_status =?" , $import_status);
        }
        if($from_date != '' && $to_date != ''){
             $from_date = Util::returnDateFormatted($from_date, "d-m-Y", "Y-m-d", "-","-",'from'); 
             $to_date = Util::returnDateFormatted($to_date, "d-m-Y", "Y-m-d", "-","-",'from'); 
             $select->where("fi.date_created >= '" . $from_date . "'");
             $select->where("fi.date_created <= '" . $to_date . "'");
        }
        
        $select->order('ai.id ASC');
        
        return $this->fetchAll($select);
        
        
    }
    
    
    public function exportgetagentimportreport($param) {

        $data = $this->getagentimportreport($param);
        
        $retData = array();

        if (!empty($data)) {
            foreach ($data as $key => $data) {
                
                $retData[$key]['distributor_code'] = $data['distributor_code'];
                $retData[$key]['title'] = strtoupper($data['title']);
                $retData[$key]['first_name'] = ucfirst($data['first_name']);
                $retData[$key]['middle_name'] = $data['middle_name'];;
                $retData[$key]['last_name'] = $data['last_name'];
                $retData[$key]['email'] = $data['email'];
                $retData[$key]['mobile'] = $data['mobile'];
                $retData[$key]['mobile2'] = $data['mobile2'];
                $retData[$key]['institution_name'] = ucfirst($data['institution_name']);
                $retData[$key]['centre_id'] = $data['centre_id'];
                $retData[$key]['terminal_id_1'] = $data['terminal_id_1'];
                $retData[$key]['terminal_id_2'] = $data['terminal_id_2'];
                $retData[$key]['terminal_id_3'] = $data['terminal_id_3'];
                $retData[$key]['education_level'] = $data['education_level'];
                $retData[$key]['matric_school_name'] = ucfirst($data['matric_school_name']);
                $retData[$key]['intermediate_school_name'] = ucfirst($data['intermediate_school_name']);
                $retData[$key]['graduation_degree'] = $data['graduation_degree'];;
                $retData[$key]['graduation_college'] = ucfirst($data['graduation_college']);
                $retData[$key]['post_graduation_degree'] = $data['post_graduation_degree'];
                $retData[$key]['post_graduation_college'] = $data['post_graduation_college'];
                $retData[$key]['other_degree'] = $data['other_degree'];
                $retData[$key]['other_college'] = $data['other_college'];
                $retData[$key]['date_of_birth'] = Util::returnDateFormatted($data['date_of_birth'], "Y-m-d", "d-m-Y", "-");
                $retData[$key]['gender'] = strtoupper($data['gender']);
                $retData[$key]['Identification_type'] = $data['Identification_type'];
                $retData[$key]['Identification_number'] = $data['Identification_number'];
                $retData[$key]['passport_expiry'] = $data['passport_expiry'];
                $retData[$key]['address_proof_type'] = $data['address_proof_type'];
                $retData[$key]['address_proof_number'] = $data['address_proof_number'];
                $retData[$key]['pan_number'] = $data['pan_number'];;
                $retData[$key]['establishment_name'] = $data['establishment_name'];
                $retData[$key]['establishment_address1'] = $data['establishment_address1'];
                $retData[$key]['establishment_address2'] = $data['establishment_address2'];
                $retData[$key]['establishment_city'] = $data['establishment_city'];
                $retData[$key]['establishment_taluka'] = $data['establishment_taluka'];
                $retData[$key]['establishment_district'] = $data['establishment_district'];
                $retData[$key]['establishment_state'] = $data['establishment_state'];
                $retData[$key]['establishment_country'] = $data['establishment_country'];
                $retData[$key]['establishment_pincode'] = $data['establishment_pincode'];
                $retData[$key]['residence_type'] = $data['residence_type'];
                $retData[$key]['residence_address1'] = $data['residence_address1'];
                $retData[$key]['residence_address2'] = $data['residence_address2'];
                $retData[$key]['residence_city'] = $data['residence_city'];;
                $retData[$key]['residence_taluka'] = $data['residence_taluka'];
                $retData[$key]['residence_district'] = $data['residence_district'];
                $retData[$key]['residence_state'] = $data['residence_state'];
                $retData[$key]['residence_country'] = $data['residence_country'];
                $retData[$key]['residence_pincode'] = $data['residence_pincode'];
                $retData[$key]['bank_name'] = $data['bank_name'];
                $retData[$key]['bank_account_number'] = $data['bank_account_number'];
                $retData[$key]['bank_location'] = $data['bank_location'];
                $retData[$key]['bank_city'] = $data['bank_city'];
                $retData[$key]['bank_ifsc_code'] = $data['bank_ifsc_code'];
                $retData[$key]['Linked_branch_id'] = $data['Linked_branch_id'];
                $retData[$key]['bank_area'] = $data['bank_area'];
                $retData[$key]['import_status'] = strtoupper($data['import_status']);
                $retData[$key]['failed_message'] = $data['failed_message'];
                
            }
        }

        return $retData;
    }
    
    
}