<?php

class CustPurseBalance extends App_Model
{
    
   
      
    public function updateCustPurseClosingbalance($param) 
    {  
        $bankUnicodeArr = Util::bankUnicodesArray();
        $detailsArr = array();
        if (!isset($param['bank_unicode']) || $param['bank_unicode'] == '') {
            $param['bank_unicode'] = $bankUnicodeArr['3'];
        }
       
        switch ($param['bank_unicode']) {
//            case $bankUnicodeArr['3']:
//                $objCardload = new Corp_Kotak_CustomerPurse();
//                $detailsArr = $objCardload->updateClosingBalance();
//                break;
            case $bankUnicodeArr['2']:
            default:
                $objCardload = new Corp_Ratnakar_CustomerPurse();
                $detailsArr = $objCardload->updateClosingBalance();
                break;
//            case $bankUnicodeArr['1']:
//                $objCardload = new Corp_Boi_CustomerPurse();
//                $detailsArr = $objCardload->updateClosingBalance();
//                break;
        }
        return $detailsArr;
        
    }
}