<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 *
 * Contao Module "Visitors" - DCA Helper Class DcaVisitorsCategory
 *
 * @copyright  Glen Langer 2012..2017 <http://contao.ninja>
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
 * DCA Helper Class DcaVisitorsCategory
 *
 * @copyright  Glen Langer 2012..2017 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 *
 */
class DcaVisitorsCategory extends \Backend 
{
    public function labelCallback($arrRow)
	{
		$label_1 = $arrRow['title'];
		$label_2 = ' <span style="color: #B3B3B3;">[ID:'.$arrRow['id'].']</span>';
		$version_warning = '';
		return $label_1 . $label_2 . $version_warning;//. '<br /><span style="color:#b3b3b3;">['.$label_2.']</span>';
	}

	public function getAdminCheckbox($varValue)
	{
        return '1';
	}
}
