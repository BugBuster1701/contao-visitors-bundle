<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2018 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 * 
 * Modul Visitors - Backend DCA tl_module
 *
 * This file modifies the data container array of table tl_module.
 *
 * PHP version 5
 * @copyright  Glen Langer 2009..2018
 * @author     Glen Langer
 * @license    LGPL
 * @filesource
 */

/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['visitors']   = '{title_legend},name,type,headline;{config_legend},visitors_categories,visitors_template;{protected_legend:hide},protected;{expert_legend:hide},visitors_useragent,guests,cssID';

/**
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
    'default'                 => 'mod_visitors_fe_all',
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('BugBuster\Visitors\DcaModuleVisitors', 'getVisitorsTemplates'),
    'explanation'	          => 'visitors_help_module',
    'sql'                     => "varchar(32) NOT NULL default ''",
    'eval'                    => array('tl_class'=>'w50', 'helpwizard'=>true)
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
