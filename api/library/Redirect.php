<?php

/*

 * To change this license header, choose License Headers in Project Properties.

 * To change this template file, choose Tools | Templates

 * and open the template in the editor.

 */



/**

 * Description of Redirect

 *

 * @author kenny
 * @Modifield by  dsaved

 */

class Redirect {

public static function to($location = null){

	if($location){

		if(is_numeric($location)){

		    switch($location){

			  case 404:

			  header('HTTP/1.0 404 Not Found');

			  include'includes/errors/404.php';

			  exit();

			  break;
			}
		}
		if (headers_sent() )    // Check if the headers were already sent
		{
		    echo( "<script>document.location.href='$location';</script>" );  // Use client side redirect
		    die();  // Stop here
		}
		else
		{
		    header('Location: '. $location); // Use server side redirect
		}
	}

  }
}

