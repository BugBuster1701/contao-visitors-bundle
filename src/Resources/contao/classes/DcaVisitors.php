<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2017 Leo Feyer
 *
 * Contao Module "Visitors" - DCA Helper Class DcaVisitors
 *
 * @copyright  Glen Langer 2012..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/contao-visitors-bundle
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Image;
use Contao\StringUtil;
use Psr\Log\LogLevel;

/**
 * DCA Helper Class DcaVisitors
 *
 * @copyright  Glen Langer 2012..2022 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 */
class DcaVisitors extends \Contao\Backend 
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('Contao\BackendUser', 'User');
    }

    public function listVisitors($arrRow)
    {
        $key = $arrRow['published'] ? 'published' : 'unpublished';
        if (!\strlen($arrRow['visitors_startdate'])) {
            $startdate = $GLOBALS['TL_LANG']['tl_visitors']['not_defined'];
        } else {
            $startdate = date (\Contao\Config::get('dateFormat'), $arrRow['visitors_startdate']);
        }
        $output = '<div class="cte_type ' . $key . '"><span class="tl_label">' . $arrRow['visitors_name'] . '</span></div>';
        $output.= '<div>'.$GLOBALS['TL_LANG']['tl_visitors']['visitors_startdate'][0].': ' . $startdate . '</div>';
        //Debug $output.= '<div>'.print_r($arrRow,true).'</div>';
        return $output;
    }

    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (\strlen(\Contao\Input::get('tid')))
        {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') == 1));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_visitors::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.svg';
        }

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';
    }

    /**
     * Disable/enable a counter
     * @param integer
     * @param boolean
     */
    public function toggleVisibility($intId, $blnVisible)
    {
        // Check permissions to publish
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_visitors::published', 'alexf'))
        {
            \Contao\System::getContainer()
                ->get('monolog.logger.contao')
                ->log(
                    LogLevel::ERROR,
                    'Not enough permissions to publish/unpublish Visitors ID "'.$intId.'"',
                    array('contao' => new ContaoContext('tl_visitors toggleVisibility', ContaoContext::ERROR))
                );

            $this->redirect('contao/main.php?act=error');
        }

        // Update database
        \Contao\Database::getInstance()->prepare("UPDATE 
                                               tl_visitors 
                                           SET 
                                               published='" . ($blnVisible ? 1 : '') . "' 
                                           WHERE 
                                               id=?")
                                ->execute($intId);
    }
}
