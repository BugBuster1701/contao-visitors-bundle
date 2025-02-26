<?php

/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2019 Leo Feyer
 * 
 * Modul Visitors Config File
 *
 * @copyright  Glen Langer 2009..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

\define('VISITORS_VERSION', '1.8');
\define('VISITORS_BUILD', '7');

/**
 * Backend css version number are added to style sheets files to make
 * the web browser reload those resources after a Visitors update.
 */
\define('VISITORS_BE_CSS', '1.2.2');

/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */
$GLOBALS['BE_MOD']['content']['visitors'] = array
(
	'tables'     => array('tl_visitors_category', 'tl_visitors'),
	'icon'       => 'bundles/bugbustervisitors/iconVisitor.png',
	'stylesheet' => 'bundles/bugbustervisitors/mod_visitors_be_'.VISITORS_BE_CSS.'.css'
);

$GLOBALS['BE_MOD']['system']['visitorstat'] = array
(
	'callback'   => 'BugBuster\Visitors\ModuleVisitorStat',
	'icon'       => 'bundles/bugbustervisitors/iconVisitor.png',
	'stylesheet' => 'bundles/bugbustervisitors/mod_visitors_be_'.VISITORS_BE_CSS.'.css'
);

/**
 * -------------------------------------------------------------------------
 * FRONT END MODULES
 * -------------------------------------------------------------------------
 */
//$GLOBALS['FE_MOD']['miscellaneous']['visitors'] = 'BugBuster\Visitors\ModuleVisitors';

/**
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('BugBuster\Visitors\ModuleVisitorsTag', 'replaceInsertTagsVisitors');
