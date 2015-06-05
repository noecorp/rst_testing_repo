<?php
/**
 * Model for HIC kotak Product
 *
 * @author Vikram Singh
 * @package HIC Ratnakar
 * @copyright transerv
 */

abstract class Corp_Boi extends Corp
{
    Const PRODUCT_NAME = 'BOI Customer';
    
    public function mediAssistIdProofType($typeId){
          
        switch($typeId){
           case '01':
              $str = 'Passport';
              break;
           case '02':
               $str = 'PAN card';
              break;
           case '03':
              $str = 'Aadhar card';
              break;
           case '04':
               $str = 'Driving license';
              break;
           case '05':
             $str = 'Government approved ID card';
              break;
       }
        return $str;
    }
    
     public function mediAssistAddressProofType($typeId){

        switch($typeId){
           case '01':
              $str = 'Passport';
              break;
           case '02':
               $str = 'Bank account statement';
              break;
           case '03':
              $str = 'Electricity bill';
              break;
           case '04':
               $str = 'Ration card';
              break;
           case '05':
             $str = 'Government approved Address Proof';
              break;
       }
        return $str;
    }
}