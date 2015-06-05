<?php

class Track extends App_Model {

    /**
     * Column for the primary key
     *
     * @var string
     * @access protected
     */
    //protected $_primary = 'id';

    
    protected function getCustomerObject($productId) {
        $productInfo = $this->validateProductCode($productId);
        //echo '<pre>';print_r($productInfo->toArray());exit;
        //echo $productInfo->const;exit;
        if(!empty($productInfo)) {
            switch ($productInfo->const) {
                case 'BOI_NSDC' :
                    $obj = new Corp_Boi_Customers();
                    break;
                case 'KOTAK_AMUL' :
                    $obj = new Corp_Kotak_Customers();
                    break;
                case 'RATNAKAR_MEDIASSIST' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                case 'RAT_PAYTRONIC' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                case 'RAT_COPASS' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                case 'RAT_HAPPAY' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                case 'RAT_CNERGYIS' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                case 'KOTAK_OPENLOOP_GPR' :
                    $obj = new Corp_Kotak_Customers();
                    break;
                case 'KOTAK_SEMICLOSE_GPR' :
                    $obj = new Corp_Kotak_Customers();
                    break;
                case 'RAT_GENERIC_GPR' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                case 'RAT_SMP' :
                    $obj = new Corp_Ratnakar_Cardholders();
                    break;
                default :
                    return FALSE;
                    
            }
            return $obj;
        }
        return FALSE;
    }
    
    
   protected function validateProductCode($productCode) {
        $productModel = new Products();
        $rs = $productModel->isActiveProduct($productCode);
        if(!empty($rs)) {
            return $rs;
        }
        return FALSE;
    }
      
   
    protected function getCustomerPurseObject($productId) {
        $productInfo = $this->validateProductCode($productId);
      
        if(!empty($productInfo)) {
            switch ($productInfo->const) {
                case 'BOI_NSDC' :
                    $obj = new Corp_Boi_CustomerPurse();
                    break;
                case 'KOTAK_AMUL' :
                    $obj = new Corp_Kotak_CustomerPurse();
                    break;
                case 'RATNAKAR_MEDIASSIST' :
                    $obj = new Corp_Ratnakar_CustomerPurse();
                    break;
                case 'RAT_PAYTRONIC' :
                    $obj = new Corp_Ratnakar_CustomerPurse();
                    break;
                case 'RAT_COPASS' :
                    $obj = new Corp_Ratnakar_CustomerPurse();
                    break;
                case 'RAT_HAPPAY' :
                    $obj = new Corp_Ratnakar_CustomerPurse();
                    break;
                case 'RAT_CNERGYIS' :
                    $obj = new Corp_Ratnakar_CustomerPurse();
                    break;
                case 'KOTAK_OPENLOOP_GPR' :
                    $obj = new Corp_Kotak_CustomerPurse();
                    break;
                case 'KOTAK_SEMICLOSE_GPR' :
                    $obj = new Corp_Kotak_CustomerPurse();
                    break;
                default :
                    return FALSE;
                    
            }
            return $obj;
        }
        return FALSE;
    }
    
}
