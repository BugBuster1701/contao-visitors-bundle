<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace BugBuster\Visitors;

use Symfony\Component\HttpFoundation\Response;


/**
 * Back end alerts wizard.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class FrontendVisitors extends \Frontend
{


	/**
	 * Initialize the object (do not remove)
	 */
	public function __construct()
	{
		parent::__construct();

		// See #4099
		if (!defined('BE_USER_LOGGED_IN'))
		{
			define('BE_USER_LOGGED_IN', false);
		}

		if (!defined('FE_USER_LOGGED_IN'))
		{
			define('FE_USER_LOGGED_IN', false);
		}
	}


	/**
	 * Run the controller
	 *
	 * @return Response
	 */
	public function run()
	{

	    
	    
	    $objResponse = new Response('<h1>Hi</h1>'/* buffer */);   		
		return $objResponse;
	}
}
