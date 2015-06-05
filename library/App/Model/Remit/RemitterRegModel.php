<?php


class Remit_RemitterRegModel extends App_Model
{

    public static $length = 10;

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
    protected $_name = DbTable::TABLE_RATNAKAR_REMITTERS;

    public function getUnregisteredRemittersAtRbl() {

        $select = $this->_db->select();
        $select->from(DbTable::TABLE_RATNAKAR_REMITTERS . " as r", array('id','mobile', 'name', 'middle_name', 'last_name', 'address', 'pincode', 'city', 'state', 'remitterid'));
        $select->joinLeft(DbTable::TABLE_AGENTS . " as a", "r.by_agent_id=a.id", array('bcagent'));
        //TABLE_AGENTS
        $select->where("r.remitterid IS NULL OR r.remitterid = 0");
        return $this->_db->fetchAll($select);
    }


    public function initSession($bcagent){
        $session = new Zend_Session_Namespace("App.Agent.Controller");
        $session->bcagent = $bcagent;
        $this->doLogin($session);
        return $session;
    }

    private function doLogin($session){
            error_log('Doing login as session');
    		$rblApiObject = new App_Rbl_Api();

            $rbiResponse = $rblApiObject->channelPartnerLogin(array('username' => RBL_CHANNEL_PARTNER_LOGIN_USERNAME,
                            'password' => RBL_CHANNEL_PARTNER_LOGIN_PASSWORD,
                            'bcagent' => $session->bcagent));

            if(isset($rbiResponse['status']) && $rbiResponse['status']) {
                    $session->rblSessionID = $rbiResponse['sessiontoken'];
            }
    }

    	// create remitter in the RBL
	public function processRemitterRegistrationRequest($remitterData,$session) {

		$dataToRblRemitterRegister = $this->_prepareRemiterDataToRblApi($remitterData,$session);//fixme.. get agent

		$rblApiObject = new App_Rbl_Api();
		$rblRemiterRegisterRespose = $rblApiObject->remitterRegistrationRemittance($dataToRblRemitterRegister);
		
        error_log('Mobile: '. $remitterData['mobile'] . ' , RemitterId: ' . $rblRemiterRegisterRespose['remitterid']);

		if(isset($rblRemiterRegisterRespose['status']) && $rblRemiterRegisterRespose['status']) {
				$this->saveRemitterID($rblRemiterRegisterRespose['remitterid'],$remitterData['id']);	
		}
		else if(isset($rblRemiterRegisterRespose['status']) && $rblRemiterRegisterRespose['status'] == 0
									&& $rblRemiterRegisterRespose['description'] == 'MOBILE NUMBER ALREADY REGISTERED') {
										$rblRemitterDetailsResponse = $this->getRemitterDetails($remitterData,$session);
									error_log($rblRemitterDetailsResponse['remitterdetail']['remitterid']);
									
									if(isset($remitterDetailArr['status']) && $remitterDetailArr['status']) {
										$this->saveRemitterID($rblRemitterDetailsResponse['remitterdetail']['remitterid'],$remitterData['id']);
									}
		}
		
		return $rblRemiterRegisterRespose;

	}

	private function getRemitterDetails($remitterData,$session){
		error_log("Getting remitter details from rbl");
		
		$session = new Zend_Session_Namespace("App.Agent.Controller");
		$data = array('header' => array('sessiontoken' => $session->rblSessionID),
				'bcagent' => $session->bcagent,
				'mobilenumber' => $remitterData['mobile'],
				'flag' => 1);
		
		$rblApiObject = new App_Rbl_Api();
		$rblRemitterDetails = $rblApiObject->remitterDetails($data);
		return $rblRemitterDetails;
	}


		// save rbl Remitter id in the datbase.
	protected function saveRemitterID($id,$where) {
		$remitterModelObject = new Remit_Ratnakar_Remitter();
		return $remitterModelObject->updateRemitter(array('remitterid' => $id),$where);
	}


    public function _prepareRemiterDataToRblApi($remitterData,$session) {

	 	return array('header' => array('sessiontoken' => $session->rblSessionID),
								'bcagent' => $session->bcagent,
								'remittermobilenumber' => $remitterData['mobile'],
								'remittername' => $remitterData['name'].' '.$remitterData['middle_name'].' '.$remitterData['last_name'],
								'remitteraddress' => $remitterData['address'],
								'remitteraddress1' => $remitterData['address'],
								'pincode' => $remitterData['pincode'],
								'cityname' => $remitterData['city'],
								'statename' => $remitterData['state'],
								'alternatenumber' => $remitterData['mobile'],
								'idproof' => '',
								'idproofnumber' => '',
								'idproofissuedate' => '',
								'idproofexpirydate' => '',
								'idproofissueplace' => '',
								'lremitteraddress' => $remitterData['address'],
								'lpincode' => $remitterData['pincode'],
								'lstatename' => $remitterData['state'],
								'lcityname' => $remitterData['city']);
  }


}

