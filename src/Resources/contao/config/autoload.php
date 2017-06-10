<?php

/**
 * Contao Open Source CMS, Copyright (c) 2005-2017 Leo Feyer
 *
 * @package Visitors
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


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
	'mod_visitors_be_stat_details_referrer'                => 'vendor/bugbuster/contao-visitors-bundle/src/Resources/contao/templates',
));
