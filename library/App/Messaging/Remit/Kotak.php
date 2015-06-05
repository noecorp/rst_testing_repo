<?php
namespace App\Messaging\Remit;
/**
 * Remit\BOI
 * Class to handle all Messaging for Remit BOI Product
 * @author Vikram
 */
class Kotak extends \App\Messaging\Remit {
    
    public function __construct($product) {
        parent::__construct($product);
    }
}
