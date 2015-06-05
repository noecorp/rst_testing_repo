<?php
/**
 * Ops can view Reports
 *
 * @package frontend_controllers
 * @copyright company
 */

class ReportsController extends App_Operation_Controller
{
	/**
	 * Overrides Zend_Controller_Action::init()
	 *
	 * @access public
	 * @return void
	 */
	public function init()
	{

		// init the parent
		parent::init();
		 

	}

	/**
	 * Controller's entry point
	 *
	 * @access public
	 * @return void
	 */
	public function indexAction(){
		 
	}

	public function corporatefundingAction(){
		$this->title = 'Corporate Funding Report';
		// Get our form and validate it
		$form = new CorporateFundingReportForm(array('action' => $this->formatURL('/reports/corporatefunding'),
				'method' => 'POST',
		));
		$user = Zend_Auth::getInstance()->getIdentity();
		$page = $this->_getParam('page');
		$request = $this->_getAllParams();
		$qurStr['to_date'] = $this->_getParam('to_date');
		$qurStr['from_date'] = $this->_getParam('from_date');
		$qurStr['sub'] = $this->_getParam('sub');


		if($qurStr['sub']!=''){
			$formData  = $this->_request->getPost();
			if($form->isValid($qurStr)){
				if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
					$qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
					$qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-");
					$durationDates = Util::getDurationRangeAllDates($qurData);
					$from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");
					$to = Util::returnDateFormatted($qurData['to'], "Y-m-d", "d-m-Y", "-");
					$fromArr = explode(" ",$from);
					$toArr = explode(" ",$to);
					$this->view->title = 'Corporate Funding Report for '.$fromArr[0];
					$this->view->title .= ' to '.$toArr[0];
					$this->view->from = $qurData['from'];
					$this->view->to   = $qurData['to'];
				}
				$objReports = new CorporateFunding();
				$fundingDetails = $objReports->getCorporateFunding($durationDates, $user->id);
				$this->view->paginator = $objReports->paginateByArray($fundingDetails, $page, $paginate);
			}
			$this->view->formData = $qurStr;
			 
		}
		$this->view->form = $form;

	}


	/* exportcorporatefundrequestsAction function is responsible to create the csv file on fly with agent fund requests report data
	 * and let user download that file.
	*/

	public function exportcorporatefundingAction(){

		// Get our form and validate it
		$form = new CorporateFundingReportForm(array('action' => $this->formatURL('/reports/corporatefunding'),
				'method' => 'POST',
		));
		 
		$user = Zend_Auth::getInstance()->getIdentity();
		$qurStr['dur'] = $this->_getParam('dur');
		$qurStr['to_date'] = $this->_getParam('to_date');
		$qurStr['from_date'] = $this->_getParam('from_date');


		 
		if($form->isValid($qurStr)){
			if ($qurStr['to_date'] != '' && $qurStr['from_date'] != '') {
				$qurData['to'] = Util::returnDateFormatted($qurStr['to_date'], "d-m-Y", "Y-m-d", "-", "-");
				$qurData['from'] = Util::returnDateFormatted($qurStr['from_date'], "d-m-Y", "Y-m-d", "-", "-");
				$durationDates = Util::getDurationRangeAllDates($qurData);
				$from = Util::returnDateFormatted($qurData['from'], "Y-m-d", "d-m-Y", "-");

			}
			$qurData['agent_id'] = $user->id;
			 
			$objReports = new CorporateFunding();
			$fundingDetails = $objReports->getCorporateFunding($durationDates, $user->id);

			$columns = array(
					'Transaction Date',
					'Transfer Type',
					'Txn Code',
					'Amount',
					'Status',
					'Remarks',
			);

			$objCSV = new CSV();
			try{
				$resp = $objCSV->export($fundingDetails, $columns, 'corporate_funding');exit;
			}
			catch (Exception $e) {
				App_Logger::log($e->getMessage() , Zend_Log::ERR);
				$this->_helper->FlashMessenger( array('msg-error' => $e->getMessage(),) );
				$this->_redirect($this->formatURL('/reports/corporatefunding?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date']));
			}
			 
		} else {
			$this->_helper->FlashMessenger( array('msg-error' => 'Invalid data found!') );
			$this->_redirect($this->formatURL('/reports/corporatefunding?sub=1&to_date='.$qurStr['to_date'].'&from_date='.$qurStr['from_date']));
		}

	}

	private function filterProductArrayForForm($productInfo) {
		$productArr = array('' =>'Select Product');
		if (!empty($productInfo)) {
			foreach ($productInfo as $product) {
				$productArr[$product['product_id']] = $product['product_name'];
			}
		}
		return $productArr;
	}
}