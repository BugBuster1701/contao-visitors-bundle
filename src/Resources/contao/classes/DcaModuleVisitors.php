<?php

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;

use Contao\Backend;

/**
 * DCA Helper Class DcaModuleVisitors
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 */
class DcaModuleVisitors extends Backend
{
	public function getVisitorsTemplates($dc)
	{
		return $this->getTemplateGroup('mod_visitors_fe_');
	}
}
