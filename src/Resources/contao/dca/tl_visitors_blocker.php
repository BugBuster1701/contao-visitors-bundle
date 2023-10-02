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

$GLOBALS['TL_DCA']['tl_visitors_blocker'] = array
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
		'visitors_tstamp' => array
		(
			'sql'       => "timestamp NULL"
		),
		'visitors_ip' => array
		(
			'sql'       => "varchar(40) NOT NULL default '0.0.0.0'"
		),
		'visitors_type' => array
		(
			'sql'       => "char(1) NOT NULL default 'v'"
		)
	)
);
