<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

session_start();
/**
 * Routing
 */
$router = new Core\Router();


// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');
$router->add('login', ['controller' => 'Login', 'action' => 'new']);   
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']); 
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('signup/activate/{token:[\da-f]+}',['controller' => 'Signup', 'action' => 'activate']);
//$router->add('{controller}/{action}/#addnew');
$router->dispatch($_SERVER['QUERY_STRING']);

// class User 
// {
//     protected ?int $id = null;
//     public function __construct(string $id)
//     {
//         $this->id = $id; 
//     }
//     public function getName(): ?int
//     {
//         return $this->id;
//     }
// }

// class Settings extends User 
// {
//     public function useChildMethod(){
//         echo "I am child method called";
//     }
// }

// $user = new User(21);
// var_dump($user->getName());
// echo "<br>";
// var_dump($user);

// $profileUser = new Settings(21);
// echo "<br>";
// var_dump($profileUser->getName());
// // $profileUser->useChildMethod();
// $user->useChildMethod();