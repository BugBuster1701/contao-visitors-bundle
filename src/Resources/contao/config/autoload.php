<?php

/**
 * Contao Open Source CMS, Copyright (c) 2005-2015 Leo Feyer
 *
 * @package Visitors
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'BugBuster',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'BugBuster\Visitors\ModuleVisitorStat'              => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/modules/ModuleVisitorStat.php',
	'BugBuster\Visitors\ModuleVisitors'                 => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/modules/ModuleVisitors.php',

	// Public
	'BugBuster\Visitors\ModuleVisitorsCount'            => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/public/ModuleVisitorsCount.php',
	'BugBuster\Visitors\ModuleVisitorsScreenCount'      => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/public/ModuleVisitorsScreenCount.php',
	'BugBuster\Visitors\ModuleVisitorReferrerDetails'   => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/public/ModuleVisitorReferrerDetails.php',

	// Classes
	'BugBuster\Visitors\ModuleVisitorBrowser3'          => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorBrowser3.php',
	'BugBuster\Visitors\ModuleVisitorStatScreenCounter' => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorStatScreenCounter.php',
	'BugBuster\Visitors\DcaModuleVisitors'              => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/DcaModuleVisitors.php',
	'BugBuster\Visitors\DcaVisitors'                    => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/DcaVisitors.php',
	'BugBuster\Visitors\DcaVisitorsCategory'            => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/DcaVisitorsCategory.php',
	'BugBuster\Visitors\ModuleVisitorStatPageCounter'   => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorStatPageCounter.php',
	'BugBuster\Visitors\ModuleVisitorSearchEngine'      => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorSearchEngine.php',
	'BugBuster\Visitors\ModuleVisitorsTag'              => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorsTag.php',
	'BugBuster\Visitors\ModuleVisitorLog'               => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorLog.php',
	'BugBuster\Visitors\ModuleVisitorReferrer'          => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorReferrer.php',
	'BugBuster\Visitors\ModuleVisitorCharts'            => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorCharts.php',
	'BugBuster\Visitors\ModuleVisitorChecks'            => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/ModuleVisitorChecks.php',
	'BugBuster\Visitors\Stat\Export\VisitorsStatExport' => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/VisitorsStatExport.php',
	'BugBuster\Visitors\ForceUTF8\Encoding'             => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/classes/Encoding.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_visitors_fe_invisible'                            => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat_partial_pagevisithittop'         => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat_partial_pagevisithitdays'        => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_fe_all'                                  => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_fe_hits'                                 => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat_partial_screentopresolution'     => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat_partial_screentopresolutiondays' => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_error'                                   => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_fe_visits'                               => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat_partial_pagevisithittoday'       => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat_partial_pagevisithityesterday'   => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
	'mod_visitors_be_stat'                                 => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
));
