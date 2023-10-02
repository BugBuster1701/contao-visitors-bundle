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

$GLOBALS['TL_DCA']['tl_visitors_pages'] = array
(
	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id'  => 'primary',
				'vid,visitors_page_date,visitors_page_id,visitors_page_type' => 'unique'
			)
		)
	),

	// Fields
	'fields' => array
	(
		'id'     => array
		(
			'sql'         => "int(10) unsigned NOT NULL auto_increment"
		),
		'vid'                 => array
		(
			'sql'         => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_page_date'  => array
		(
			'sql'         => "date NOT NULL default '1999-01-01'"
		),
		'visitors_page_id'    => array
		(
			'sql'         => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_page_pid'    => array
		(
			'sql'         => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_page_type'  => array
		(
			'sql'         => "tinyint(1) NOT NULL default '0'"
		),
		'visitors_page_visit' => array
		(
			'sql'         => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_page_hit'   => array
		(
			'sql'         => "int(10) unsigned NOT NULL default '0'"
		),
		'visitors_page_lang'  => array
		(
			'sql'         => "varchar(5) NOT NULL default ''"
		)
	)
);
