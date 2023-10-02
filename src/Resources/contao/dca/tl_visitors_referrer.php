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

$GLOBALS['TL_DCA']['tl_visitors_referrer'] = array
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
		'tstamp' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_referrer_dns' => array
		(
			'sql'       => "varchar(255) NOT NULL default '-'"
		),
		'visitors_referrer_full' => array
		(
			'sql'       => "text NULL"
		)
	)
);
