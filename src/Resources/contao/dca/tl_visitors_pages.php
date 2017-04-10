<?php

/**
 * Table tl_visitors_browser
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
                'visitors_page_type'  => array
                (
                        'sql'         => "tinyint(3) unsigned NOT NULL default '0'",
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
