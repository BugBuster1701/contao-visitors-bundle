<?php 

/**
 * Extension for Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 * 
 * Visitors - Backend DCA tl_visitors
 *
 * This is the data container array for table tl_visitors.
 *
 * @copyright  Glen Langer 2009..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @licence    LGPL
 * @filesource
 * @package    GLVisitors
 * @see	       https://github.com/BugBuster1701/visitors
 */

/**
 * Table tl_visitors 
 */
$GLOBALS['TL_DCA']['tl_visitors'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_visitors_category',
		'enableVersioning'            => true,
        'sql' => array
        (
            'keys' => array
            (
                'id'  => 'primary',
                'pid' => 'index'
            )
        )
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'filter'                  => true,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'search,filter,limit',
			'headerFields'            => array('title', 'tstamp'), //, 'visitors_template'
			'child_record_callback'   => array('BugBuster\Visitors\DcaVisitors', 'listVisitors')
		),/**
		'label' => array
		(
			'fields'                  => array(''),
			'format'                  => '%s'
		),**/
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_visitors']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_visitors']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_visitors']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'toggle' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_visitors']['toggle'],
                'icon'                => 'visible.gif',
                'attributes'          => 'onclick="Backend.getScrollOffset(); return AjaxRequest.toggleVisibility(this, %s);"',
                'button_callback'     => array('BugBuster\Visitors\DcaVisitors', 'toggleIcon')
            ),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_visitors']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		//'__selector__'                => array(''),
		'default'                     => '{title_legend},visitors_name,visitors_startdate;{start_legend:hide},visitors_visit_start,visitors_hit_start;{average_legend},visitors_average,visitors_block_time;{design_legend},visitors_thousands_separator;{statistic_legend},visitors_statistic_days;{publish_legend},published;{visitors_expert_legend:hide},visitors_expert_debug_tag,visitors_expert_debug_checks,visitors_expert_debug_referrer,visitors_expert_debug_searchengine,visitors_expert_debug_screenresolutioncount'
	),

	// Subpalettes
	/**'subpalettes' => array
	(
		''                            => ''
	),**/

	// Fields
	'fields' => array
	(
    	'id' => array
    	(
    	        'sql'       => "int(10) unsigned NOT NULL auto_increment"
    	),
    	'pid' => array
    	(
    	        'sql'       => "int(10) unsigned NOT NULL default '0'"
    	),
    	'sorting' => array
    	(
    	        'sql'       => "int(10) unsigned NOT NULL default '0'"
    	),
    	'tstamp' => array
    	(
    	        'sql'       => "int(10) unsigned NOT NULL default '0'"
    	),
	    'visitors_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_name'],
			'inputType'               => 'text',
			'search'                  => true,
			'explanation'	          => 'visitors_help',
			'sql'                     => "varchar(64) NOT NULL default ''",
			'eval'                    => array('mandatory'=>true, 'maxlength'=>40, 'helpwizard'=>true, 'tl_class'=>'w50')
		),
		'visitors_startdate' => array
		(
			'exclude'                 => true,
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_startdate'],
			'inputType'               => 'text',
			'explanation'	          => 'visitors_help',
			'sql'                     => "varchar(10) NOT NULL default ''",
			'eval'                    => array('maxlength'=>10, 'rgxp'=>'date', 'helpwizard'=>true, 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard')
		),
		'visitors_visit_start' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_visit_start'],
			'inputType'               => 'text',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'eval'                    => array('mandatory'=>false, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>false, 'tl_class'=>'w50')
		),
		'visitors_hit_start'   => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_hit_start'],
			'inputType'               => 'text',
			'sql'                     => "int(10) unsigned NOT NULL default '0'",
			'eval'                    => array('mandatory'=>false, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>false, 'tl_class'=>'w50')
		),
		'visitors_average'   => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_average'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''",
			'eval'					  => array('tl_class'=>'w50')
		),
		'visitors_block_time'	=> array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_block_time'],
			'inputType'               => 'text',
			'sql'                     => "int(10) unsigned NOT NULL default '1800'",
			'eval'                    => array('mandatory'=>true, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>false, 'tl_class'=>'w50 w50h')
		),
		'visitors_thousands_separator'=> array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_thousands_separator'],
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''",
			'eval'                    => array('mandatory'=>false, 'helpwizard'=>false)
		),
		'visitors_statistic_days'     => array
		(
		    'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_statistic_days'],
		    'inputType'               => 'text',
		    'sql'                     => "int(10) unsigned NOT NULL default '14'",
		    'eval'                    => array('mandatory'=>false, 'maxlength'=>10, 'rgxp'=>'digit', 'helpwizard'=>false, 'tl_class'=>'w50 w50h'),
		    'save_callback' => array
		    (
                function($varValue, DataContainer $dc) 
                {
                    if ($varValue < 14) { $varValue = 14; }
                    if ($varValue > 99) { $varValue = 99; }
                    return $varValue;
                }
		    )
		),
		'published' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_visitors']['published'],
			'exclude'                 => true,
			'filter'                  => true,
			'flag'                    => 2,
			'inputType'               => 'checkbox',
			'sql'                     => "char(1) NOT NULL default ''",
			'eval'                    => array('doNotCopy'=>true)
		),
		'visitors_expert_debug_tag'=> array
		(
		        'label'					  => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_expert_debug_tag'],
		        'inputType'               => 'checkbox',
		        'sql'                     => "char(1) NOT NULL default ''",
		        'eval'                    => array('mandatory'=>false, 'helpwizard'=>false)
		),
		'visitors_expert_debug_checks'=> array
		(
		        'label'					  => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_expert_debug_checks'],
		        'inputType'               => 'checkbox',
		        'sql'                     => "char(1) NOT NULL default ''",
		        'eval'                    => array('mandatory'=>false, 'helpwizard'=>false)
		),
		'visitors_expert_debug_referrer'=> array
		(
		        'label'					  => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_expert_debug_referrer'],
		        'inputType'               => 'checkbox',
		        'sql'                     => "char(1) NOT NULL default ''",
		        'eval'                    => array('mandatory'=>false, 'helpwizard'=>false)
		),
		'visitors_expert_debug_searchengine'=> array
		(
		        'label'					  => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_expert_debug_searchengine'],
		        'inputType'               => 'checkbox',
		        'sql'                     => "char(1) NOT NULL default ''",
		        'eval'                    => array('mandatory'=>false, 'helpwizard'=>false)
		),
		'visitors_expert_debug_screenresolutioncount'=> array
		(
		        'label'					  => &$GLOBALS['TL_LANG']['tl_visitors']['visitors_expert_debug_screenresolutioncount'],
		        'inputType'               => 'checkbox',
		        'sql'                     => "char(1) NOT NULL default ''",
		        'eval'                    => array('mandatory'=>false, 'helpwizard'=>false)
		)
	)
);

