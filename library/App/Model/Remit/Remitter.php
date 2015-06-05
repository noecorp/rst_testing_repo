<?php
/*
 *  Remitters Model
 */
class Remit_Remitter extends Remit
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
    protected $_name = DbTable::TABLE_REMITTERS;
    
    /**
     * Holds the associated model class
     * 
     * @var string
     * @access protected
     */
    //protected $_rowClass = 'App_Table_Remitters';
    
    
     /*
     *  getRemitterRegistrations function will fetch remitters details registred during a time span
      * depending on the bank unicode
     */
    public function getAgentRemitterRegnFee($param){
        $from = $param['from'];
        $to = $param['to'];
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getAgentRemitterRegnFee($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getAgentRemitterRegnFee($param);
                break;
            case $bankUnicodeArr['1']:
                $boiRemitModel = new Remit_Boi_Remitter();
                $detailsArr = $boiRemitModel->getAgentRemitterRegnFee($param);
                break;
        }
           
        return $detailsArr; 
    }
    
      /*
     *  Get remitter registration fee for an agent on a particular date for a product during a time span
      * depending on the bank unicode
     */
    public function getRemitterRegnfee($param){
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getRemitterRegnfeeAll($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getRemitterRegnfeeAll($param);
                break;
            case $bankUnicodeArr['1']:
                $boiRemitModel = new Remit_Boi_Remitter();
                $detailsArr = $boiRemitModel->getRemitterRegnfeeAll($param);
                break;
        }
          
        return $detailsArr; 
    }
    /*  getAgentTotalRemitterRegnFeeSTax() is responsible for fetch data for agent total remitter regn fee & Service Tax amount 
     *  as params it will accept agent id and transaction date
     */  
   public function getAgentTotalRemitterRegnFeeSTax($param){
       $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getAgentTotalRemitterRegnFeeSTax($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getAgentTotalRemitterRegnFeeSTax($param);
                break;
            case $bankUnicodeArr['1']:
                $boiRemitModel = new Remit_Boi_Remitter();
                $detailsArr = $boiRemitModel->getAgentTotalRemitterRegnFeeSTax($param);
                break;
        }
           
        return $detailsArr; 
    }
    
     /* getRemittersCount() will return the number of remitters registerd for the month
     */
    public function getRemittersCount($param){
        $from = $param['from'];
        $to = $param['to'];
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getRemittersCount($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getRemittersCount($param);
                break;
            case $bankUnicodeArr['1']:
                $boiRemitModel = new Remit_Boi_Remitter();
                $detailsArr = $boiRemitModel->getRemittersCount($param);
                break;
        }
           
        return $detailsArr; 
    }
    
    
    /* getRemittersRgnCount() will return the number of remitters registerd for the month
     */
    public function getRemittersRgnCount($param){
        $from = $param['from'];
        $to = $param['to'];
        
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getRemittersRgnCount($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getRemittersRgnCount($param);
                break;
            case $bankUnicodeArr['1']:
                $boiRemitModel = new Remit_Boi_Remitter();
                $detailsArr = $boiRemitModel->getRemittersRgnCount($param);
                break;
        }
           
        return $detailsArr; 
    }
    
    public function getAgentRemitterRegnsFeeSTax($param){
       $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3']; // default kotak
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getAgentRemitterRegnsFeeSTax($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getAgentRemitterRegnsFeeSTax($param);
                break;
        }
           
        return $detailsArr; 
    }
   
      /*
     *  Get remitter registration fee for an agent on a particular date for a product during a time span
      * depending on the bank unicode. This function is called from Ops portal. Please do not modify this function
     */
    public function getRemitterRegnfeeOps($param){
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            case $bankUnicodeArr['3']:
                $kotakRemitModel = new Remit_Kotak_Remitter();
                $detailsArr = $kotakRemitModel->getRemitterRegistrationfee($param);
                break;
            case $bankUnicodeArr['2']:
                $ratnakarRemitModel = new Remit_Ratnakar_Remitter();
                $detailsArr = $ratnakarRemitModel->getRemitterRegistrationfee($param);
                break;
            case $bankUnicodeArr['1']:
                $boiRemitModel = new Remit_Boi_Remitter();
                $detailsArr = $boiRemitModel->getRemitterRegistrationfee($param);
                break;
        }
          
        return $detailsArr; 
    }
    
    
    public function getRemitterLoadfeeOps($param){
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if(!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['1']; // default boi
        }
        switch($param['bank_unicode'])
        {
            
            case $bankUnicodeArr['2']:
                $ratnakarLoadModel = new Corp_Ratnakar_Cardload();
                $detailsArr = $ratnakarLoadModel->getRemitterLoadfee($param);
                break;
            
        }
          
        return $detailsArr; 
    }
}