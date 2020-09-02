<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle
 *
 * @copyright  Glen Langer 2020 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @license    LGPL-3.0-or-later
 * @see        https://github.com/BugBuster1701/contao-visitors-bundle
 */

namespace BugBuster\VisitorsBundle\Controller\FrontendModule;

use BugBuster\Visitors\ModuleVisitorLog;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Date;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\System;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class VisitorsFrontendController extends AbstractFrontendModuleController
{
    protected $strTemplate = 'mod_visitors_fe_all';
    protected $useragent_filter = '';
    protected $visitors_category = false;

    private static $_BackendUser  = false;

    const PAGE_TYPE_NORMAL     = 0;    //0   = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs
	const PAGE_TYPE_NEWS       = 1;    //1   = Nachrichten/News
	const PAGE_TYPE_FAQ        = 2;    //2   = FAQ
	const PAGE_TYPE_ISOTOPE    = 3;    //3   = Isotope
	const PAGE_TYPE_FORBIDDEN  = 403;  //403 = Forbidden Page

    /**
     * Lazyload some services.
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services['contao.framework'] = ContaoFramework::class;
        $services['database_connection'] = Connection::class;
        $services['contao.routing.scope_matcher'] = ScopeMatcher::class;
        $services['security.helper'] = Security::class;
        $services['translator'] = TranslatorInterface::class;

        return $services;
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        dump($model);
        dump($template);

        $this->useragent_filter = $model->visitors_useragent;
        $this->visitors_category = $model->visitors_categories;

        /* @var PageModel $objPage */
        global $objPage;

        if (!is_numeric($this->visitors_category)) {
            $this->strTemplate = 'mod_visitors_error';
            $template = new \Contao\FrontendTemplate($this->strTemplate);

            return $template->getResponse();
        }
        $this->visitorSetDebugSettings($this->visitors_category);

        if (false === self::$_BackendUser)
		{
    		$objTokenChecker = System::getContainer()->get('contao.security.token_checker');
    		if ($objTokenChecker->hasBackendUser())
    		{
    		    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': BackendUser: Yes');
    		    self::$_BackendUser = true;
    		} 
    		else 
    		{
    		    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ': BackendUser: No');
    		}
        }
        
        if ($this->strTemplate !== $model->visitors_template && '' !== $model->visitors_template) {
            $this->strTemplate = $model->visitors_template;
            $template = new \Contao\FrontendTemplate($this->strTemplate);
        }

        if ('mod_visitors_fe_invisible' === $this->strTemplate) {
            // invisible, but counting!
            //@todo Aufruf Zählmethode
            $arrVisitors[] = ['VisitorsKatID' => $this->visitors_category];
            $template->visitors = $arrVisitors;

            return $template->getResponse();
        }

        /* ____  __  ____________  __  ________
		  / __ \/ / / /_  __/ __ \/ / / /_  __/
		 / / / / / / / / / / /_/ / / / / / /   
		/ /_/ / /_/ / / / / ____/ /_/ / / /    
		\____/\____/ /_/ /_/    \____/ /_/ 
		*/

        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            tl_visitors.id AS id, 
                            visitors_name, 
                            visitors_startdate, 
                            visitors_visit_start, 
                            visitors_hit_start,
                            visitors_average,
                            visitors_thousands_separator
                        FROM 
                            tl_visitors 
                        LEFT JOIN 
                            tl_visitors_category ON (tl_visitors_category.id = tl_visitors.pid)
                        WHERE 
                            pid = :pid AND published = :published
                        ORDER BY id, visitors_name
                        LIMIT :limit')
        ;
        $stmt->bindValue('pid', $this->visitors_category, \PDO::PARAM_INT);
        $stmt->bindValue('published', 1, \PDO::PARAM_INT);
        $stmt->bindValue('limit', 1, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() < 1) {
            \Contao\System::getContainer()
                 ->get('monolog.logger.contao')
                 ->log(LogLevel::ERROR,
                       'VisitorsFrontendController User Error: no published counter found.',
                       ['contao' => new ContaoContext('VisitorsFrontendController getResponse ', TL_ERROR)])
        ;

            $this->strTemplate = 'mod_visitors_error';
            $template = new \Contao\FrontendTemplate($this->strTemplate);

            return $template->getResponse();
        }

        while (false !== ($objVisitors = $stmt->fetch(\PDO::FETCH_OBJ))) {
            $VisitorsStartDate = false;
            $VisitorsAverageVisits = false;
            $boolSeparator = (1 === $objVisitors->visitors_thousands_separator) ? true : false;

            if (\strlen($objVisitors->visitors_startdate)) {
                $VisitorsStartDate = Date::parse($objPage->dateFormat, $objVisitors->visitors_startdate);
            }

            if ($objVisitors->visitors_average) {
                $VisitorsAverageVisits = true;
                $VisitorsAverageVisitsValue = $this->getAverageVisits($objVisitors->id, $boolSeparator);
            }

            if (!isset($GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'])) {
                $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'] = '';
            }

            $arrVisitors[] = [
                'VisitorsNameLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'],
                'VisitorsName' => trim($objVisitors->visitors_name),

                'VisitorsKatID' => $this->visitors_category,
                'VisitorsStartDate' => $VisitorsStartDate, //false|value - ugly - i know

                'AverageVisitsLegend' => $GLOBALS['TL_LANG']['visitors']['AverageVisitsLegend'],
                'AverageVisits' => $VisitorsAverageVisits,  //bool
                'AverageVisitsValue' => $VisitorsAverageVisitsValue,

                'VisitorsOnlineCountLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsOnlineCountLegend'],
                'VisitorsOnlineCountValue' => $this->getVisitorsOnlineCount($objVisitors->id, $boolSeparator),

                'VisitorsStartDateLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsStartDateLegend'],
                'VisitorsStartDateValue' => $this->getVisitorsStartDate($objVisitors->visitors_startdate, $objPage),

                'TotalVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TotalVisitCountLegend'],
                'TotalVisitCountValue' => $this->getTotalVisitCount($objVisitors, $boolSeparator),

                'TotalHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TotalHitCountLegend'],
                'TotalHitCountValue' => $this->getTotalHitCount($objVisitors, $boolSeparator),

                'TodayVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TodayVisitCountLegend'],
                'TodayVisitCountValue' => $this->getTodaysVisitCount($objVisitors, $boolSeparator),

                'TodayHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TodayHitCountLegend'],
                'TodayHitCountValue' => $this->getTodaysHitCount($objVisitors, $boolSeparator),

                'YesterdayVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['YesterdayVisitCountLegend'],
                'YesterdayVisitCountValue' => $this->getYesterdayVisitCount($objVisitors, $boolSeparator),

                'YesterdayHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['YesterdayHitCountLegend'],
                'YesterdayHitCountValue' => $this->getYesterdayHitCount($objVisitors, $boolSeparator),

                'PageHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['PageHitCountLegend'],
            ];

            //@todo weitermachen
        }

        $template->visitors = $arrVisitors;

        $userFirstname = 'DUDE';
        $user = $this->get('security.helper')->getUser();
        if ($user instanceof FrontendUser) {
            $userFirstname = $user->firstname;
        }

        /** @var Date $dateAdapter */
        $dateAdapter = $this->get('contao.framework')->getAdapter(Date::class);
        $intWeekday = $dateAdapter->parse('w');
        $translator = $this->get('translator');
        $strWeekday = $translator->trans('DAYS.'.$intWeekday, [], 'contao_default');

        $arrGuests = [];
        $stmt = $this->get('database_connection')
            ->executeQuery(
                'SELECT * FROM tl_member WHERE gender=? ORDER BY lastname',
                ['female']
            )
        ;
        while (false !== ($objMember = $stmt->fetch(\PDO::FETCH_OBJ))) {
            $arrGuests[] = $objMember->firstname;
        }

        $template->helloTitle = sprintf(
            'Hi %s, and welcome to the "Hello World Module". Today is %s.',
            $userFirstname, $strWeekday
        );

        $template->helloText = 'Our guests today are: '.implode(', ', $arrGuests);

        return $template->getResponse();
    }

    protected function getAverageVisits($VisitorsId, $boolSeparator)
    {
        $VisitorsAverageVisits = 0;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date('m'), (int) date('d') - 1, (int) date('Y')));

        $stmt = $this->get('database_connection')
                ->prepare(
                    'SELECT 
                        SUM(visitors_visit) AS SUMV, 
                        MIN( visitors_date) AS MINDAY
                    FROM 
                        tl_visitors_counter
                    WHERE 
                        vid = :vid AND visitors_date < :vdate

                    ')
        ;
        $stmt->bindValue('vid', $VisitorsId, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', $today, \PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $objVisitorsAverageCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $tmpTotalDays = floor((strtotime($yesterday) - strtotime($objVisitorsAverageCount->MINDAY)) / 60 / 60 / 24);
            $VisitorsAverageVisitCount = (null === $objVisitorsAverageCount->SUMV) ? 0 : (int) $objVisitorsAverageCount->SUMV;
            if ($tmpTotalDays > 0) {
                $VisitorsAverageVisits = round($VisitorsAverageVisitCount / $tmpTotalDays, 0);
            }
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsAverageVisits, 0) : $VisitorsAverageVisits;
    }

    protected function getVisitorsOnlineCount($VisitorsId, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                ->prepare(
                    'SELECT 
                        COUNT(id) AS VOC 
                    FROM 
                        tl_visitors_blocker
                    WHERE 
                        vid = :vid AND visitors_type = :vtype
                    ')
        ;
        $stmt->bindValue('vid', $VisitorsId, \PDO::PARAM_INT);
        $stmt->bindValue('vtype', 'v', \PDO::PARAM_STR);
        $stmt->execute();

        $objVisitorsOnlineCount = $stmt->fetch(\PDO::FETCH_OBJ);
        $VisitorsOnlineCount = (null === $objVisitorsOnlineCount->VOC) ? 0 : $objVisitorsOnlineCount->VOC;

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsOnlineCount, 0) : $VisitorsOnlineCount;
    }

    protected function getVisitorsStartDate($VisitorsStartdate, $objPage)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        if (\strlen($VisitorsStartdate)) 
        {
            $VisitorsStartDate = Date::parse($objPage->dateFormat, $VisitorsStartdate);
        }
        else
        {
            $VisitorsStartDate = '';
        }

        return $VisitorsStartDate;
    }

    protected function getTotalVisitCount($objVisitors, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            SUM(visitors_visit) AS SUMV
                        FROM 
                            tl_visitors_counter
                        WHERE 
                            vid = :vid
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->execute();

        $VisitorsTotalVisitCount = $objVisitors->visitors_visit_start; //Startwert
        if ($stmt->rowCount() > 0) 
        {
            $objVisitorsTotalCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsTotalVisitCount += ($objVisitorsTotalCount->SUMV === null) ? 0 : $objVisitorsTotalCount->SUMV;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsTotalVisitCount, 0) : $VisitorsTotalVisitCount;
    }

    protected function getTotalHitCount($objVisitors, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            SUM(visitors_hit) AS visitors_hit
                        FROM 
                            tl_visitors_counter
                        WHERE 
                            vid = :vid
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->execute();

        $VisitorsTotalHitCount   = $objVisitors->visitors_hit_start;   //Startwert
        if ($stmt->rowCount() > 0) 
        {
            $objVisitorsTotalCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsTotalHitCount += ($objVisitorsTotalCount->SUMH === null) ? 0 : $objVisitorsTotalCount->SUMH;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsTotalHitCount, 0) : $VisitorsTotalHitCount;
    }

    protected function getTodaysVisitCount($objVisitors, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            visitors_visit
                        FROM 
                            tl_visitors_counter
                        WHERE 
                            vid = :vid AND visitors_date = :vdate
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d'), \PDO::PARAM_STR);
        $stmt->execute();

        $VisitorsTodaysVisitCount = 0;
        if ($stmt->rowCount() > 0) 
        {
            $objVisitorsTodaysCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsTodaysVisitCount = ($objVisitorsTodaysCount->visitors_visit === null) ? 0 : $objVisitorsTodaysCount->visitors_visit;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsTodaysVisitCount, 0) : $VisitorsTodaysVisitCount;
    }

    protected function getTodaysHitCount($objVisitors, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            visitors_hit
                        FROM 
                            tl_visitors_counter
                        WHERE 
                            vid = :vid AND visitors_date = :vdate
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d'), \PDO::PARAM_STR);
        $stmt->execute();

        $VisitorsTodaysHitCount = 0;
        if ($stmt->rowCount() > 0) 
        {
            $objVisitorsTodaysCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsTodaysHitCount = ($objVisitorsTodaysCount->visitors_hit === null) ? 0 : $objVisitorsTodaysCount->visitors_hit;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsTodaysHitCount, 0) : $VisitorsTodaysHitCount;
    }

    protected function getYesterdayVisitCount($objVisitors, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            visitors_visit
                        FROM 
                            tl_visitors_counter
                        WHERE 
                            vid = :vid AND visitors_date = :vdate
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d', strtotime('-1 days')), \PDO::PARAM_STR);
        $stmt->execute();

        $VisitorsYesterdayVisitCount = 0;
        if ($stmt->rowCount() > 0)
        {
            $objVisitorsYesterdayCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsYesterdayVisitCount = ($objVisitorsYesterdayCount->visitors_visit === null) ? 0 : $objVisitorsYesterdayCount->visitors_visit;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsYesterdayVisitCount, 0) : $VisitorsYesterdayVisitCount;
    }

    protected function getYesterdayHitCount($objVisitors, $boolSeparator)
    {
        //ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$arrTag[2]);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            visitors_hit
                        FROM 
                            tl_visitors_counter
                        WHERE 
                            vid = :vid AND visitors_date = :vdate
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d', strtotime('-1 days')), \PDO::PARAM_STR);
        $stmt->execute();

        $VisitorsYesterdayHitCount = 0;
        if ($stmt->rowCount() > 0)
        {
            $objVisitorsYesterdayCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsYesterdayHitCount = ($objVisitorsYesterdayCount->visitors_hit === null) ? 0 : $objVisitorsYesterdayCount->visitors_hit;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsYesterdayHitCount, 0) : $VisitorsYesterdayHitCount;
    }

    protected function getPageHits($objVisitors, $boolSeparator, $objPage)
    {
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':start');

        //if page from cache, we have no page-id
        /*
        if ($objPage->id == 0)
        {
            $objPage = $this->visitorGetPageObj();

        } //$objPage->id == 0
        */
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page ID '. $objPage->id);
        //#80, bei Readerseite den Beitrags-Alias beachten
        //0 = reale Seite / 404 / Reader ohne Parameter - Auflistung der News/FAQs
        //1 = Nachrichten/News
        //2 = FAQ
        //3 = Isotope
        //403 = Forbidden
        $visitors_page_type = $this->visitorGetPageType($objPage);
        //bei News/FAQ id des Beitrags ermitteln und $objPage->id ersetzen
        $objPageId = $this->visitorGetPageIdByType($objPage->id, $visitors_page_type, $objPage->alias);

        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            SUM(visitors_page_hit)   AS visitors_page_hits
                        FROM 
                        tl_visitors_pages
                        WHERE 
                            vid = :vid
                        AND 
                            visitors_page_id = :vpageid
                        AND
                            visitors_page_type = :vpagetype
                        ')
                    ;
        $stmt->bindValue('vid', $objVisitors->id, \PDO::PARAM_INT);
        $stmt->bindValue('vpageid', $objPageId, \PDO::PARAM_INT);
        $stmt->bindValue('vpagetype', $visitors_page_type, \PDO::PARAM_INT);
        $stmt->execute();

        $VisitorsPageHits = 0;
        if ($stmt->rowCount() > 0)
        {
            $objPageStatCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $VisitorsPageHits = $objPageStatCount->visitors_page_hits;
        }

        return ($boolSeparator) ? System::getFormattedNumber($VisitorsPageHits, 0) : $VisitorsPageHits;
    }

    ////////////////////////////////// INTERNAL /////////////////////////////////////////////7

    protected function visitorSetDebugSettings($visitors_category_id)
	{
	    $GLOBALS['visitors']['debug']['tag']          = false; 
	    $GLOBALS['visitors']['debug']['checks']       = false;
	    $GLOBALS['visitors']['debug']['referrer']     = false;
	    $GLOBALS['visitors']['debug']['searchengine'] = false;

        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT
                            visitors_expert_debug_tag,
                            visitors_expert_debug_checks,
                            visitors_expert_debug_referrer,
                            visitors_expert_debug_searchengine
                        FROM
                            tl_visitors
                        LEFT JOIN
                            tl_visitors_category ON (tl_visitors_category.id=tl_visitors.pid)
                        WHERE
                            pid = :pid AND published = :published
                        ORDER BY tl_visitors.id, visitors_name
                        ')
                    ;
        $stmt->limit(1);
        $stmt->bindValue('pid', $visitors_category_id, \PDO::PARAM_INT);
        $stmt->bindValue('published', 1, \PDO::PARAM_INT);
        $stmt->execute();

        while (false !== ($objVisitors = $stmt->fetch(\PDO::FETCH_OBJ))) 
        {
	        $GLOBALS['visitors']['debug']['tag']          = (bool) $objVisitors->visitors_expert_debug_tag;
	        $GLOBALS['visitors']['debug']['checks']       = (bool) $objVisitors->visitors_expert_debug_checks;
	        $GLOBALS['visitors']['debug']['referrer']     = (bool) $objVisitors->visitors_expert_debug_referrer;
	        $GLOBALS['visitors']['debug']['searchengine'] = (bool) $objVisitors->visitors_expert_debug_searchengine;
	        ModuleVisitorLog::writeLog('## START ##', '## DEBUG ##', 'T'.(int) $GLOBALS['visitors']['debug']['tag'] .'#C'. (int) $GLOBALS['visitors']['debug']['checks'] .'#R'.(int) $GLOBALS['visitors']['debug']['referrer'] .'#S'.(int) $GLOBALS['visitors']['debug']['searchengine']);
	    }
	}



    /**
	 * Get Page-Type
	 * 
	 * @param  object $objPage
	 * @return integer 0 = reale Seite, 1 = News, 2 = FAQ, 403 = Forbidden
	 */
	protected function visitorGetPageType($objPage)
	{
	    $PageId = $objPage->id;
	    //Return:
	    //0 = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs
	    //1 = Nachrichten/News
	    //2 = FAQ
	    //403 = Forbidden

	    $page_type = self::PAGE_TYPE_NORMAL;

	    if ($objPage->protected == 1) 
	    {
            //protected Seite. user 
            $user = $this->get('security.helper')->getUser();

            if (!$user instanceof FrontendUser) 
            {
                $page_type = self::PAGE_TYPE_FORBIDDEN;
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

	            return $page_type;
            }
	    }

        //Set the item from the auto_item parameter
        //from class ModuleNewsReader#L57
        if (!isset($_GET['items']) && \Contao\Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
        	\Contao\Input::setGet('items', \Contao\Input::get('auto_item'));
        }
        if (!\Contao\Input::get('items'))
        {
            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

            return $page_type;
        }

        $dbconnection = $this->get('database_connection');

	    //News Table exists?
        if (\Contao\Input::get('items') && $dbconnection->getSchemaManager()->tablesExist('tl_news')) 
	    {
            //News Reader?
            $stmt = $dbconnection->prepare(
                        'SELECT id 
                        FROM tl_news_archive 
                        WHERE jumpTo = :jumpto
                        ')
                    ;
            $stmt->limit(1);
            $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
            $stmt->execute();


    	    if ($stmt->rowCount() > 0)
    	    {
    	        //News Reader
                $page_type = self::PAGE_TYPE_NEWS;
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

                return $page_type;
    	    }
	    }

	    //FAQ Table exists?
	    if (\Contao\Input::get('items') && $dbconnection->getSchemaManager()->tableExists('tl_faq_category'))
	    {
            //FAQ Reader?
            $stmt = $dbconnection->prepare(
                        'SELECT id 
                        FROM tl_faq_category 
                        WHERE jumpTo = :jumpto
                        ')
                    ;
            $stmt->limit(1);
            $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
            $stmt->execute();

	        if ($stmt->rowCount > 0)
	        {
	            //FAQ Reader
                $page_type = self::PAGE_TYPE_FAQ;
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

                return $page_type;
	        }
	    }

	    //Isotope Table tl_iso_product exists?
	    if (\Contao\Input::get('items') && $dbconnection->getSchemaManager()->tableExists('tl_iso_product'))
	    {
			$strAlias = \Contao\Input::get('items');
			ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Get items: '. print_r($strAlias, true));			

            $stmt = $dbconnection->prepare(
                        'SELECT id 
                        FROM tl_iso_product 
                        WHERE alias = :alias
                        ')
                    ;
            $stmt->limit(1);
            $stmt->bindValue('alias', $strAlias, \PDO::PARAM_STR);
            $stmt->execute();

			if ($stmt->rowCount > 0)
			{
	            //Isotope Reader
                $page_type = self::PAGE_TYPE_ISOTOPE;
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

                return $page_type;
	        }
	    }

	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '. $page_type);

	    return $page_type;
    }
    
    /**
	 * Get Page-ID by Page-Type
	 * 
	 * @param  integer $PageId
	 * @param  integer $PageType
	 * @param  string  $PageAlias
	 * @return integer
	 */
	protected function visitorGetPageIdByType($PageId, $PageType, $PageAlias)
	{
	    if ($PageType == self::PAGE_TYPE_NORMAL) 
	    {
	        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNormal: '. $PageId);

	    	return $PageId;
	    }

	    if ($PageType == self::PAGE_TYPE_FORBIDDEN)
	    {
	        //Page ID von der 403 Seite ermitteln - nicht mehr
	        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNormal over 403: '. $PageId);

	        return $PageId; 
	    }

        //Reader mit Parameter oder ohne?
        $uri = $_SERVER['REQUEST_URI']; // /news/james-wilson-returns.html
        $alias = '';
        //steht suffix (html) am Ende?
        $urlSuffix = System::getContainer()->getParameter('contao.url_suffix'); // default: .html
        if (substr($uri, -\strlen($urlSuffix)) == $urlSuffix)
        {
            //Alias nehmen
            $alias = substr($uri, strrpos($uri, '/')+1, -\strlen($urlSuffix));
            if (false === $alias) 
            {
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdReaderSelf: '. $PageId);

            	return $PageId; // kein Parameter, Readerseite selbst
            }
        }
        else 
        {
            $alias = substr($uri, strrpos($uri, '/')+1);
        }
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Alias: '. $alias);

        $dbconnection = $this->get('database_connection');

        if ($PageType == self::PAGE_TYPE_NEWS)
        {
            //alias = james-wilson-returns
            $stmt = $dbconnection->prepare(
                        'SELECT id 
                        FROM tl_news 
                        WHERE alias = :alias
                        ')
                    ;
            $stmt->limit(1);
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount > 0)
            {
                $objNews = $stmt->fetch(\PDO::FETCH_OBJ);
                ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNews: '. $objNews->id);

                return $objNews->id;
            } 

	    }
	    if ($PageType == self::PAGE_TYPE_FAQ)
	    {
            //alias = are-there-exams-how-do-they-work
            $stmt = $dbconnection->prepare(
                        'SELECT id 
                        FROM tl_faq 
                        WHERE alias = :alias
                        ')
                    ;
            $stmt->limit(1);
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount > 0)
	        {
                $objFaq = $stmt->fetch(\PDO::FETCH_OBJ);
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdFaq: '. $objFaq->id);

	            return $objFaq->id;
	        }
	    }
	    if ($PageType == self::PAGE_TYPE_ISOTOPE)
	    {
            //alias = a-perfect-circle-thirteenth-step
            $stmt = $dbconnection->prepare(
                        'SELECT id 
                        FROM tl_iso_product 
                        WHERE alias = :alias
                        ')
                    ;
            $stmt->limit(1);
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount > 0)
	        {
                $objIsotope = $stmt->fetch(\PDO::FETCH_OBJ);
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdIsotope: '. $objIsotope->id);

	            return $objIsotope->id;
	        }
	    }

	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Unknown PageType: '. $PageType);
	}
}
