<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 *
 * Contao Module "Visitors" - DCA Helper Class DcaModuleVisitors
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/visitors
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors;

/**
 * DCA Helper Class DcaModuleVisitors
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 *
 */
class DcaModuleVisitors extends \Backend 
{
	public function getVisitorsTemplates($dc)
	{
	    return $this->getTemplateGroup('mod_visitors_fe_', $dc->activeRecord->pid);
	}  
}
