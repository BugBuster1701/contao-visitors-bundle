<?php 

/**
 * Table tl_visitors_counter
 */
$GLOBALS['TL_DCA']['tl_visitors_counter'] = array
(
        // Config
        'config' => array
        (
                'sql' => array
                (
                        'keys' => array
                        (
                                'id' => 'primary',
                                'vid,visitors_date' => 'unique'
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
                'visitors_date' => array
                (
                        'sql'       => "date NOT NULL default '1999-01-01'"
                ),
            	'visitors_visit' => array
            	(
            	        'sql'       => "int(10) unsigned NOT NULL default '0'"
            	),
            	'visitors_hit' => array
            	(
            	        'sql'       => "int(10) unsigned NOT NULL default '0'"
            	)
        )
);
