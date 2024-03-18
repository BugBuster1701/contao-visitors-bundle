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

$GLOBALS['TL_DCA']['tl_visitors_browser'] = array
(
	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id'  => 'primary',
				'vid' => 'index'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id' => array
		(
			'sql'       => "int(10) unsigned NOT NULL auto_increment"
		),
		'vid' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_browser' => array
		(
			'sql'       => "varchar(60) NOT NULL default 'Unknown'"
		),
		'visitors_os' => array
		(
			'sql'       => "varchar(60) NOT NULL default 'Unknown'"
		),
		'visitors_lang' => array
		(
			'sql'       => "varchar(10) NOT NULL default 'Unknown'"
		),
		'visitors_counter' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		)
	)
);
