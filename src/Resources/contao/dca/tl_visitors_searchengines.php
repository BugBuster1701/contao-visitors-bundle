<?php

/**
 * Table tl_visitors_searchengines
 */
$GLOBALS['TL_DCA']['tl_visitors_searchengines'] = array
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
                'visitors_searchengine' => array
                (
                        'sql'       => "varchar(60) NOT NULL default 'Unknown'"
                ),
                'visitors_keywords' => array
                (
                        'sql'       => "varchar(255) NOT NULL default 'Unknown'"
                )
        )
);
