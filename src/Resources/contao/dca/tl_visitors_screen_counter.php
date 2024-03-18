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

$GLOBALS['TL_DCA']['tl_visitors_screen_counter'] = array
(
	// Config
	'config' => array
	(
		'sql' => array
		(
			'keys' => array
			(
				'id' => 'primary',
				'vid,v_date,v_s_w,v_s_h,v_s_iw,v_s_ih' => 'unique'
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
		'v_date' => array
		(
			'sql'       => "date NOT NULL default '1999-01-01'"
		),
		// Screen Width
		'v_s_w' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		),
		// Screen Hight
		'v_s_h' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		),
		// Screen Inner Width
		'v_s_iw' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		),
		// Screen Inner Hight
		'v_s_ih' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		),
		'v_screen_counter' => array
		(
			'sql'       => "int(10) unsigned NOT NULL default '0'"
		)
	)
);
