<?php

//Deprecated and need to be removed
interface App_Api_Authentificator {
    
    /* Authenticate an API user based on username, password, and IP address
     *
     * $method_name The name of the method being called
     * $username Username as a string
     * $password Password as a string
     * $ip IP address in dotted quad
     *
     * return value: Instance of APISession
     * exceptions: APIAuthError
     */    
    public function authenticateAPIUser($method_name, $username, $password, $ip);
}
