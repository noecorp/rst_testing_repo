<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=shmart;host=localhost',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
//        'factories' => array(
//            'Zend\Db\Adapter\Adapter'
//                    => 'Zend\Db\Adapter\AdapterServiceFactory',
//        ),
        'factories' => array(
    'AuthService' => function($sm) {
        $auth = new \Zend\Authentication\AuthenticationService();
        return $auth;
    },
    /*'RoleMapper' => function($sm) {
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $mapper = new \Application\Mapper\RoleMapper;
        $mapper->setDbAdapter($dbAdapter);
        $mapper->setEntityPrototype(new \Application\Model\Role);
        $mapper->setHydrator(new \Application\Model\RoleHydrator);

        return $mapper;
    },*/
    'UserMapper' => function($sm) {
        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
        $mapper = new \MyModule\Mapper\UserMapper;
        $mapper->setDbAdapter($dbAdapter);
        $mapper->setEntityPrototype(new \MyModule\Model\User);
        $mapper->setHydrator($sm->get('UserHydrator'));

        return $mapper;
    },
    ),
    ),
);