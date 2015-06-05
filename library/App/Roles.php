<?php
namespace App;

/**
 * Messaging
 * Default parent Messaging for all the messages in the application
 *
 * @category App
 * @author Vikram
 */
abstract class Roles {

    private $_error = null;

    /**
     * Messaging Constructor
     * @param type $product
     */
    public function __construct($product) {
    }
    
    /**
     * __set Magic method
     * Set Value if not doesn't exsits
     * @param type $name
     * @param type $value
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * __get Magic Method
     * Used to get Value if not found get it from the class object
     * @param type $name
     * @return type
     */
    public function __get($name) {
        if(isset($this->$name)) {
            return $this->$name;
        }
    }

    /**
     * setValue
     * Method to set value
     * @param type $name
     * @param type $value
     */
    public function setValue($name, $value) {
        $this->$name = $value;
    }

    /**
     * getValue
     * Method to get value form class object
     * @param type $name
     * @return type
     */
    public function getValue($name) {
        if(isset($this->$name)) {
            return $this->$name;
        }
    }


}