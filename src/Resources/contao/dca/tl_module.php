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

/*
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['visitors']   = '{title_legend},name,type,headline;{config_legend},visitors_categories,visitors_template,visitors_update;{protected_legend:hide},protected;{expert_legend:hide},visitors_useragent,guests,cssID';

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['visitors_categories'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['visitors_categories'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'              => 'tl_visitors_category.title',
	'sql'                     => "varchar(255) NOT NULL default ''",
	'eval'                    => array('multiple'=>false, 'mandatory'=>true, 'tl_class'=>'w50')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['visitors_template'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['visitors_template'],
	'default'                 => 'mod_visitors_fe_invisible',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('BugBuster\Visitors\DcaModuleVisitors', 'getVisitorsTemplates'),
	'explanation'	          => 'visitors_help_module',
	'sql'                     => "varchar(32) NOT NULL default ''",
	'eval'                    => array('tl_class'=>'w50', 'helpwizard'=>true)
);
$GLOBALS['TL_DCA']['tl_module']['fields']['visitors_update'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['visitors_update'],
	'inputType'               => 'text',
	'explanation'	          => 'visitors_help_module',
	'sql'                     => "int(10) unsigned NOT NULL default '10'",
	'eval'                    => array('mandatory'=>false, 'maxlength'=>10, 'rgxp'=>'natural', 'minval'=>1, 'helpwizard'=>false, 'tl_class'=>'w50 w50h')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['visitors_useragent'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['visitors_useragent'],
	'inputType'               => 'text',
	'search'                  => true,
	'explanation'	          => 'visitors_help_module',
	'sql'                     => "varchar(64) NOT NULL default ''",
	'eval'                    => array('mandatory'=>false, 'maxlength'=>64, 'helpwizard'=>true)
);
