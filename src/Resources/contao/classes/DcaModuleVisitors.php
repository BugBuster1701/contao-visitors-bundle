<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * Contao Module "Visitors" - DCA Helper Class DcaModuleVisitors
 *
 * @copyright  Glen Langer 2012..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;

/**
 * DCA Helper Class DcaModuleVisitors
 *
 * @copyright  Glen Langer 2012..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 */
class DcaModuleVisitors extends \Backend 
{
	public function getVisitorsTemplates($dc)
	{
	    return $this->getTemplateGroup('mod_visitors_fe_');
	}  
}
