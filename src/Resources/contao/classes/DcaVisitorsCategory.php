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
 * DCA Helper Class DcaVisitorsCategory
 *
 * @copyright  Glen Langer 2023 <http://contao.ninja>
 */
class DcaVisitorsCategory extends Backend
{
	public function labelCallback($arrRow)
	{
		$label_1 = $arrRow['title'];
		$label_2 = ' <span style="color: #B3B3B3;">[ID:' . $arrRow['id'] . ']</span>';
		$version_warning = '';

		return $label_1 . $label_2 . $version_warning; // . '<br /><span style="color:#b3b3b3;">['.$label_2.']</span>';
	}

	public function getAdminCheckbox($varValue)
	{
		return '1';
	}
}
