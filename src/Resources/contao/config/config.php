<?php

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 */

define('VISITORS_VERSION', '1.15');
define('VISITORS_BUILD', '2');

/*
 * Backend css version number are added to style sheets files to make
 * the web browser reload those resources after a Visitors update.
 */
define('VISITORS_BE_CSS', '1.2.3');

/*
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */
$GLOBALS['BE_MOD']['content']['visitors'] = array
(
	'tables'     => array('tl_visitors_category', 'tl_visitors'),
	'icon'       => 'bundles/bugbustervisitors/iconVisitor.png',
	'stylesheet' => 'bundles/bugbustervisitors/mod_visitors_be_' . VISITORS_BE_CSS . '.css'
);

$GLOBALS['BE_MOD']['system']['visitorstat'] = array
(
	'callback'   => 'BugBuster\Visitors\ModuleVisitorStat',
	'icon'       => 'bundles/bugbustervisitors/iconVisitor.png',
	'stylesheet' => 'bundles/bugbustervisitors/mod_visitors_be_' . VISITORS_BE_CSS . '.css'
);

/*
 * -------------------------------------------------------------------------
 * FRONT END MODULES CSS Minimum Definitions
 * (ugly hack, in FE esi request, no isFrontendRequest is available)
 * -------------------------------------------------------------------------
 */
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

if (!System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create('')))
{
	$GLOBALS['TL_CSS'][] = 'bundles/bugbustervisitors/mod_visitors_basic.css|static';
}

/*
 * -------------------------------------------------------------------------
 * HOOKS
 * -------------------------------------------------------------------------
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('BugBuster\Visitors\ModuleVisitorsTag', 'replaceInsertTagsVisitors');
