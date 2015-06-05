<?php

/*
 * Validator class for AuthRequest
 * 
 */
class Api_Switch extends Api
{
    private $error_msg = '';
    private $error_code = '';

    public function validateProductCode($productCode)
    {
        $product = App_DI_Definition_BankProduct::getInstance(BANK_BOI_NDSC);                
        $productModel = new Products();
        $productInfo = $productModel->getProductInfoByUnicode($product->product->unicode);
        $productInfo = Util::toArray($productInfo);
        if($productInfo['ecs_product_code'] != $productCode) {
            $this->setError(App_ApiServer_Exchange::$INVALID_RESPONSE, 'Product Code ' . $productCode . ' not allowed');
            return FALSE;
        }
        return TRUE;
        
    }
    
    
   
    public function validateCRN($param) 
    {
        if($this->validateProductCode($param['productCode'])) {
            if($this->validateProductCode($param['productCode'])) {
                $ecsApi = new App_Api_ECS_Transactions();
                $resp =  $ecsApi->stopCard(array(
                    'crn'   => $param['crn']));                
                if($resp) {
                    $cardholderModel = new Corp_Boi_Customers();
                    $cardholderModel->blockCard(array('card_number' => $param['crn']));
                    return TRUE;
                } else {
                    $this->setError(App_ApiServer_Exchange::$INVALID_RESPONSE, $ecsApi->getError());
                    return FALSE;
                }
            } else {
                $this->setError(App_ApiServer_Exchange::$INVALID_RESPONSE, 'Cardholder Not found!!');
                return FALSE;
            }
        } 
        return FALSE;
    }
    
    public function blockAccount($param) 
    {
        if($this->validateProductCode($param['productCode'])) {
            if($this->validateProductCode($param['productCode'])) {
                $ecsApi = new App_Api_ECS_Transactions();
                $resp =  $ecsApi->stopCard(array(
                    'crn'   => $param['crn']));                
                if($resp) {
                    $cardholderModel = new Corp_Boi_Customers();
                    $cardholderModel->blockCard(array('card_number' => $param['crn']));
                    return TRUE;
                } else {
                    $this->setError(App_ApiServer_Exchange::$INVALID_RESPONSE, $ecsApi->getError());
                    return FALSE;
                }
            } else {
                $this->setError(App_ApiServer_Exchange::$INVALID_RESPONSE, 'Cardholder Not found!!');
                return FALSE;
            }
        } 
        return FALSE;
    }
    
    
    
    
}
