<?php 

/**
 * Contao Open Source CMS, Copyright (C) 2005-2014 Leo Feyer
 *
 * Contao Module "Visitors" - DCA Helper Class DcaVisitors
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 * @license    LGPL
 * @filesource
 * @see	       https://github.com/BugBuster1701/visitors
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors;

/**
 * DCA Helper Class DcaVisitors
 *
 * @copyright  Glen Langer 2012..2014 <http://www.contao.glen-langer.de>
 * @author     Glen Langer (BugBuster)
 * @package    GLVisitors
 *
 */
class DcaVisitors extends \Backend 
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }
    
    public function listVisitors($arrRow)
    {
        $key = $arrRow['published'] ? 'published' : 'unpublished';
        if (!strlen($arrRow['visitors_startdate'])) {
            $startdate = $GLOBALS['TL_LANG']['tl_visitors']['not_defined'];
        } else {
            $startdate = date($GLOBALS['TL_CONFIG']['dateFormat'], $arrRow['visitors_startdate']);
        }
        $output = '<div class="cte_type ' . $key . '"><strong>' . $arrRow['visitors_name'] . '</strong></div>' ;
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
        if (strlen(\Input::get('tid')))
        {
            $this->toggleVisibility(\Input::get('tid'), (\Input::get('state') == 1));
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
            $icon = 'invisible.gif';
        }
    
        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
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
            $this->log('Not enough permissions to publish/unpublish Visitors ID "'.$intId.'"', 'tl_visitors toggleVisibility', TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
    
        // Update database
        \Database::getInstance()->prepare("UPDATE 
                                               tl_visitors 
                                           SET 
                                               published='" . ($blnVisible ? 1 : '') . "' 
                                           WHERE 
                                               id=?")
                                ->execute($intId);
    }
}
