<?php
//modo de depuracion de errores
//error_reporting(E_WARNING | E_ERROR);
error_reporting(E_ERROR);
ini_set('display_errors', 'On');

define( '__DEBUGSQL__', false);
define( '__DEBUGREQUEST__', false);

function print_debug_request(){
	if ( __DEBUGREQUEST__ ) {
		
		var_dump($_GET);
		var_dump($_POST);
	}	
}