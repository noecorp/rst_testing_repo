<?php
/*
 * Cardload
 */
class Corp_Cardload extends Corp
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
    protected $_name = DbTable::TABLE_RAT_CORP_LOAD_REQUEST;
    
  
    public function getAgentTotalLoad($param) {
        $detailsArr = array();
        $bankUnicodeArr = Util::bankUnicodesArray();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['2']; // default rat
        }
        switch ($param['bank_unicode']) {
            case $bankUnicodeArr['2']:
            default:
                $corpModel = new Corp_Ratnakar_Cardload();
                $detailsArr = $corpModel->getAgentTotalLoad($param);
                break;
           
        }

        return $detailsArr;
    }
}
