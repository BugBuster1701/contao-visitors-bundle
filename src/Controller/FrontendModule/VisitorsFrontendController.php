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

use BugBuster\Visitors\ModuleVisitorBrowser3;
use BugBuster\Visitors\ModuleVisitorChecks;
use BugBuster\Visitors\ModuleVisitorLog;
use BugBuster\Visitors\ModuleVisitorReferrer;
use BugBuster\Visitors\ModuleVisitorSearchEngine;
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

    private $_BOT = false;	// Bot

	private $_SE  = false;	// Search Engine

	private $_PF  = false;	// Prefetch found

	private $_VB  = false;	// Visit Blocker

	private $_VisitCounted = false;

    private $_HitCounted   = false;
    
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

        System::loadLanguageFile('tl_visitors');

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

        $counting = ($this->setCounters($objPage)) ? '<!-- counted -->' : '<!-- not counted -->';

        if ('mod_visitors_fe_invisible' === $this->strTemplate) {
            // invisible, but counting!
            $arrVisitors[] = ['VisitorsKatID' => $this->visitors_category, 'VisitorsCounting' => $counting];
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
                'PageHitCountValue' => $this->getPageHits($objVisitors, $boolSeparator, $objPage)
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$VisitorsId);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$VisitorsStartdate);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id);
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
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objVisitors->id.':'.$objPage->id);

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

    protected function setCounters($objPage)
    {
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, ':'.$objPage->id);
        $stmt = $this->get('database_connection')
                    ->prepare(
                        'SELECT 
                            tl_visitors.id AS id, 
                            visitors_block_time
                        FROM 
                            tl_visitors 
                        LEFT JOIN 
                            tl_visitors_category ON (tl_visitors_category.id = tl_visitors.pid)
                        WHERE 
                            pid = :pid AND published = :published
                        ORDER BY id, visitors_name
                        LIMIT 1
                        ')
                    ;
        $stmt->bindValue('pid', $this->visitors_category, \PDO::PARAM_INT);
        $stmt->bindValue('published', 1, \PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() < 1)
        {
            System::getContainer()
                    ->get('monolog.logger.contao')
                    ->log(LogLevel::ERROR,
                        $GLOBALS['TL_LANG']['tl_visitors']['wrong_katid'],
                        array('contao' => new ContaoContext('VisitorsFrontendController setCounters '. VISITORS_VERSION .'.'. VISITORS_BUILD, TL_ERROR)));

            return false;
        }
        while (false !== ($objVisitors = $stmt->fetch(\PDO::FETCH_OBJ))) 
        {
            $this->visitorCountUpdate($objVisitors->id, $objVisitors->visitors_block_time, $this->visitors_category, self::$_BackendUser);
            $this->visitorCheckSearchEngine($objVisitors->id);
            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'BOT: '.(int) $this->_BOT);
            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'SE : '.(int) $this->_SE);
            if ($this->_BOT === false && $this->_SE === false) 
            {
                $this->visitorCheckReferrer($objVisitors->id);
            }
        }
        ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Counted Server: True');

        return true; 
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
                        LIMIT 1
                        ')
                    ;
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
                        LIMIT 1
                        ')
                    ;
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
                        LIMIT 1
                        ')
                    ;
            $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
            $stmt->execute();

	        if ($stmt->rowCount() > 0)
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
                        LIMIT 1
                        ')
                    ;
            $stmt->bindValue('alias', $strAlias, \PDO::PARAM_STR);
            $stmt->execute();

			if ($stmt->rowCount() > 0)
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
                        LIMIT 1
                        ')
                    ;
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0)
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
                        LIMIT 1
                        ')
                    ;
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0)
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
                        LIMIT 1
                        ')
                    ;
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0)
	        {
                $objIsotope = $stmt->fetch(\PDO::FETCH_OBJ);
	            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdIsotope: '. $objIsotope->id);

	            return $objIsotope->id;
	        }
	    }

	    ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Unknown PageType: '. $PageType);
    }
    
    /**
	 * Insert/Update Counter
	 */
	protected function visitorCountUpdate($vid, $BlockTime, $visitors_category_id, $BackendUser = false)
	{
		$ModuleVisitorChecks = new ModuleVisitorChecks($BackendUser);
		if (!isset($GLOBALS['TL_CONFIG']['mod_visitors_bot_check']) || $GLOBALS['TL_CONFIG']['mod_visitors_bot_check'] !== false) 
		{
			if ($ModuleVisitorChecks->checkBot() === true) 
			{
				$this->_BOT = true;

		    	return; //Bot / IP gefunden, wird nicht gezaehlt
		    }
		}
	    if ($ModuleVisitorChecks->checkUserAgent($visitors_category_id) === true) 
	    {
	    	$this->_PF = true; // Bad but functionally

	    	return; //User Agent Filterung
	    }
	    //Debug log_message("visitorCountUpdate count: ".$this->Environment->httpUserAgent,"useragents-noblock.log");
	    $ClientIP = bin2hex(sha1($visitors_category_id . $ModuleVisitorChecks->visitorGetUserIP(), true)); // sha1 20 Zeichen, bin2hex 40 zeichen
	    $BlockTime = ($BlockTime == '') ? 1800 : $BlockTime; //Sekunden
        $CURDATE = date('Y-m-d');
        
        $dbconnection = $this->get('database_connection');

        //Visitor Blocker
        $stmt = $dbconnection->prepare(
                                'DELETE FROM tl_visitors_blocker 
                                WHERE CURRENT_TIMESTAMP - INTERVAL :blocktime SECOND > visitors_tstamp
                                AND vid = :vid 
                                AND visitors_type = :vtype
                                ')
                            ;
        $stmt->bindValue('blocktime', $BlockTime, \PDO::PARAM_INT);
        $stmt->bindValue('vid', $vid, \PDO::PARAM_INT);
        $stmt->bindValue('vtype', 'v', \PDO::PARAM_STR);
        $stmt->execute();

        //Hit Blocker for IE8 Bullshit and Browser Counting
        // 3 Sekunden Blockierung zw. Zählung per Tag und Zählung per Browser
        $stmt = $dbconnection->prepare(
                                'DELETE FROM tl_visitors_blocker 
                                WHERE CURRENT_TIMESTAMP - INTERVAL :blocktime SECOND > visitors_tstamp
                                AND vid = :vid 
                                AND visitors_type = :vtype
                                ')
                            ;
        $stmt->bindValue('blocktime', 3, \PDO::PARAM_INT);
        $stmt->bindValue('vid', $vid, \PDO::PARAM_INT);
        $stmt->bindValue('vtype', 'h', \PDO::PARAM_STR);
        $stmt->execute();
        
        if ($ModuleVisitorChecks->checkBE() === true) 
	    {
	    	$this->_PF = true; // Bad but functionally

			return; // Backend eingeloggt, nicht zaehlen (Feature: #197)
		}

        //Test ob Hits gesetzt werden muessen (IE8 Bullshit and Browser Counting)
        $objHitIP = $dbconnection->prepare(
                            'SELECT 
                                id, 
                                visitors_ip
                            FROM 
                                tl_visitors_blocker
                            WHERE 
                                visitors_ip = :vip
                            AND vid = :vid
                            AND visitors_type = :vtype
                            ')
                        ;
        $objHitIP->bindValue('vip', $ClientIP   , \PDO::PARAM_STR);
        $objHitIP->bindValue('vid', $vid        , \PDO::PARAM_INT);
        $objHitIP->bindValue('vtype', 'h'       , \PDO::PARAM_STR);
        $objHitIP->execute();

        //Hits und Visits lesen
        $objHitCounter = $dbconnection->prepare(
                            'SELECT 
                                id, 
                                visitors_hit, 
                                visitors_visit
                            FROM 
                                tl_visitors_counter
                            WHERE 
                                visitors_date = :vdate AND vid = :vid
                            ')
                        ;
        $objHitCounter->bindValue('vdate', $CURDATE , \PDO::PARAM_STR);
        $objHitCounter->bindValue('vid', $vid       , \PDO::PARAM_INT);
        $objHitCounter->execute();

        //Hits setzen
	    if ($objHitCounter->rowCount() < 1) 
	    {
	    	if ($objHitIP->rowCount() < 1) 
	    	{
                //at first: block
                $stmt = $dbconnection->prepare(
                                    'INSERT INTO 
                                        tl_visitors_blocker
                                    SET 
                                        vid = :vid, 
                                        visitors_tstamp = CURRENT_TIMESTAMP, 
                                        visitors_ip = :vip, 
                                        visitors_type = :vtype
                                    ')
                                ;
                $stmt->bindValue('vid', $vid        , \PDO::PARAM_INT);
                $stmt->bindValue('vip', $ClientIP   , \PDO::PARAM_INT);
                $stmt->bindValue('vtype', 'h'       , \PDO::PARAM_STR);
                $stmt->execute();
                
                // Insert
                $stmt = $dbconnection->prepare(
                                'INSERT IGNORE INTO 
                                    tl_visitors_counter
                                SET 
                                    vid = :vid, 
                                    visitors_date = :vdate, 
                                    visitors_visit = :vv, 
                                    visitors_hit = :vh
                                ')
                            ;
                $stmt->bindValue('vid', $vid        , \PDO::PARAM_INT);
                $stmt->bindValue('vdate', $CURDATE  , \PDO::PARAM_STR);
                $stmt->bindValue('vv', 1            , \PDO::PARAM_INT);
                $stmt->bindValue('vh', 1            , \PDO::PARAM_INT);
                $stmt->execute();
                /*
		        $arrSet = array
	            (
	                'vid'               => $vid,
	                'visitors_date'     => $CURDATE,
	                'visitors_visit'    => 1,
	                'visitors_hit'      => 1
                ); 
                
			    \Database::getInstance()
			            ->prepare("INSERT IGNORE INTO tl_visitors_counter %s")
                        ->set($arrSet)
                        ->execute();
                */
			    //for page counter
			    $this->_HitCounted = true;
	    	} 
	    	else 
	    	{
	    		$this->_PF = true; // Prefetch found
	    	}
		    $visitors_hits = 1;
		    $visitors_visit = 1;
	    } 
	    else 
	    {
            $objHitCounterResult = $objHitCounter->fetch(\PDO::FETCH_OBJ);
	        $visitors_hits = $objHitCounterResult->visitors_hit +1;
	        $visitors_visit= $objHitCounterResult->visitors_visit +1; 
			if ($objHitIP->rowCount() < 1) 
			{
                // Update
                $stmt = $dbconnection->prepare(
                                    'INSERT INTO 
                                        tl_visitors_blocker
                                    SET 
                                        vid = :vid, 
                                        visitors_tstamp = CURRENT_TIMESTAMP, 
                                        visitors_ip = :vip, 
                                        visitors_type = :vtype
                                    ')
                                ;
                $stmt->bindValue('vid', $vid        , \PDO::PARAM_INT);
                $stmt->bindValue('vip', $ClientIP   , \PDO::PARAM_INT);
                $stmt->bindValue('vtype', 'h'       , \PDO::PARAM_STR);
                $stmt->execute();

                $stmt = $dbconnection->prepare(
                                'UPDATE 
                                    tl_visitors_counter 
                                SET 
                                    visitors_hit = :vhit 
                                WHERE 
                                    id = :vid
                                ')
                            ;
                $stmt->bindValue('vhit', $visitors_hits         , \PDO::PARAM_INT);
                $stmt->bindValue('vid', $objHitCounterResult->id, \PDO::PARAM_INT);
                $stmt->execute();

                //for page counter
		    	$this->_HitCounted = true;
			} 
			else 
			{
	    		$this->_PF = true; // Prefetch found
	    	}
	    }

        //Visits / IP setzen
        $objVisitIP = $dbconnection->prepare(
                    'SELECT 
                        id, 
                        visitors_ip
                    FROM 
                        tl_visitors_blocker
                    WHERE 
                        visitors_ip = :vip 
                    AND vid = :vid
                    AND visitors_type = :vtype
                    ')
                ;
        $objVisitIP->bindValue('vip', $ClientIP   , \PDO::PARAM_INT);
        $objVisitIP->bindValue('vid', $vid        , \PDO::PARAM_INT);        
        $objVisitIP->bindValue('vtype', 'v'       , \PDO::PARAM_STR);
        $objVisitIP->execute();

	    if ($objVisitIP->rowCount() < 1) 
	    {
            // not blocked: Insert IP + Update Visits
            $stmt = $dbconnection->prepare(
                            'INSERT INTO 
                                tl_visitors_blocker
                            SET 
                                vid = :vid, 
                                visitors_tstamp = CURRENT_TIMESTAMP, 
                                visitors_ip = :vip, 
                                visitors_type = :vtype
                            ')
                        ;
            $stmt->bindValue('vid', $vid        , \PDO::PARAM_INT);
            $stmt->bindValue('vip', $ClientIP   , \PDO::PARAM_INT);
            $stmt->bindValue('vtype', 'v'       , \PDO::PARAM_STR);
            $stmt->execute();

            $stmt = $dbconnection->prepare(
                            'UPDATE 
                                tl_visitors_counter 
                            SET 
                                visitors_visit = :vvis 
                            WHERE 
                                vid = :vid
                            AND 
                                visitors_date = :vdate
                            ')
                        ;
            $stmt->bindValue('vvis', $visitors_visit, \PDO::PARAM_INT);
            $stmt->bindValue('vid', $vid            , \PDO::PARAM_INT);
            $stmt->bindValue('vdate', $CURDATE      , \PDO::PARAM_STR);
            $stmt->execute();
            
            //for page counter
	        $this->_VisitCounted = true;
	    } 
	    else 
	    {
            // blocked: Update tstamp
            $stmt = $dbconnection->prepare(
                            'UPDATE 
                                tl_visitors_blocker
                            SET 
                                visitors_tstamp = CURRENT_TIMESTAMP 
                            WHERE
                                vid = :vid
                            AND
                                visitors_ip = :vip
                            AND
                                visitors_type = :vtype
                            ')
                        ;
            $stmt->bindValue('vid', $vid        , \PDO::PARAM_INT);
            $stmt->bindValue('vip', $ClientIP   , \PDO::PARAM_INT);
            $stmt->bindValue('vtype', 'v'       , \PDO::PARAM_STR);
            $stmt->execute();

            $this->_VB = true;
	    }

	    //Page Counter 
	    if ($this->_HitCounted === true || $this->_VisitCounted === true) 
	    {
    	    /** @var PageModel $objPage */
    	    global $objPage;
    	    //if page from cache, we have no page-id
    	    //if ($objPage->id == 0) 
    	    //{
    	    //    $objPage = $this->visitorGetPageObj();
            //} //$objPage->id == 0
            ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page ID / Lang in Object: '. $objPage->id .' / '.$objPage->language);

	 	    //#102, bei Readerseite den Beitrags-Alias zählen (Parameter vorhanden)
	 	    //0 = reale Seite / 404 / Reader ohne Parameter - Auflistung der News/FAQs
            //1 = Nachrichten/News
            //2 = FAQ
            //3 = Isotope
            //403 = Forbidden
	 	    $visitors_page_type = $this->visitorGetPageType($objPage);
	 	    //bei News/FAQ id des Beitrags ermitteln und $objPage->id ersetzen
	 	    //Fixed #211, Duplicate entry in tl_search
	 	    $objPageIdOrg = $objPage->id;
	 	    $objPageId    = $this->visitorGetPageIdByType($objPage->id, $visitors_page_type, $objPage->alias);

	 	    if (self::PAGE_TYPE_ISOTOPE != $visitors_page_type) {
	 	        $objPageIdOrg = 0; //backward compatibility
	 	    }
    	    $objPageHitVisit = $dbconnection->prepare(
                                            'SELECT
                                                id,
                                                visitors_page_visit,
                                                visitors_page_hit
                                            FROM
                                                tl_visitors_pages
                                            WHERE
                                                visitors_page_date = :vpagedate
                                            AND
                                                vid = :vid
                                            AND
                                                visitors_page_id = :vpageid
                                            AND
                                                visitors_page_pid = :vpagepid
                                            AND
                                                visitors_page_lang = :vpagelang
                                            AND
                                                visitors_page_type = :vpagetype
                                            ')
                                    ;
            $objPageHitVisit->bindValue('vpagedate', $CURDATE           , \PDO::PARAM_STR);
            $objPageHitVisit->bindValue('vid', $vid                     , \PDO::PARAM_INT);
            $objPageHitVisit->bindValue('vpageid', $objPageId           , \PDO::PARAM_INT);
            $objPageHitVisit->bindValue('vpagepid', $objPageIdOrg       , \PDO::PARAM_INT);
            $objPageHitVisit->bindValue('vpagelang', $objPage->language , \PDO::PARAM_STR);
            $objPageHitVisit->bindValue('vpagetype', $visitors_page_type, \PDO::PARAM_INT);
            $objPageHitVisit->execute();

    	    // eventuell $GLOBALS['TL_LANGUAGE']
    	    // oder      $objPage->rootLanguage; // Sprache der Root-Seite
    	    if ($objPageHitVisit->rowCount() < 1)
    	    {
    	        if ($objPageId > 0) 
    	        {
                    //Page Counter Insert
                    $stmt = $dbconnection->prepare(
                                    'INSERT IGNORE INTO 
                                        tl_visitors_pages
                                    SET 
                                        vid = :vid, 
                                        visitors_page_date  = :vpagedate,
                                        visitors_page_id    = :vpageid,
                                        visitors_page_pid   = :vpagepid,
                                        visitors_page_type  = :vpagetype,
                                        visitors_page_visit = :vpagevis,
                                        visitors_page_hit   = :vpagehit,
                                        visitors_page_lang  = :vpagelang
                                    ')
                                ;
                    $stmt->bindValue('vid', $vid                        , \PDO::PARAM_INT);
                    $stmt->bindValue('vpagedate', $CURDATE              , \PDO::PARAM_STR);
                    $stmt->bindValue('vpageid', $objPageId              , \PDO::PARAM_INT);
                    $stmt->bindValue('vpagepid', $objPageIdOrg          , \PDO::PARAM_INT);
                    $stmt->bindValue('vpagetype', $visitors_page_type   , \PDO::PARAM_INT);
                    $stmt->bindValue('vpagevis', 1                      , \PDO::PARAM_INT);
                    $stmt->bindValue('vpagehit', 1                      , \PDO::PARAM_INT);
                    $stmt->bindValue('vpagelang', $objPage->language    , \PDO::PARAM_STR);
                    $stmt->execute();
                    /*
        	        $arrSet = array
        	        (
        	            'vid'                 => $vid,
        	            'visitors_page_date'  => $CURDATE,
        	            'visitors_page_id'    => $objPageId,
        	            'visitors_page_pid'   => $objPageIdOrg,
        	            'visitors_page_type'  => $visitors_page_type,
        	            'visitors_page_visit' => 1,
        	            'visitors_page_hit'   => 1,
        	            'visitors_page_lang'  => $objPage->language
        	        );
        	        \Database::getInstance()
                    	        ->prepare("INSERT IGNORE INTO tl_visitors_pages %s")
                    	        ->set($arrSet)
                                ->execute();
                    */
    	        }
    	    }
    	    else
    	    {
                $objPageHitVisitResult = $objPageHitVisit->fetch(\PDO::FETCH_OBJ);
    	        $visitors_page_hits   = $objPageHitVisitResult->visitors_page_hit;
    	        $visitors_page_visits = $objPageHitVisitResult->visitors_page_visit;

    	        if ($this->_HitCounted === true)
    	        {
        	        //Update Hit
    	            $visitors_page_hits += 1;
    	        }
    	        if ($this->_VisitCounted === true)
    	        {
    	            //Update Visit
    	            $visitors_page_visits += 1;    	            
                }
                $stmt = $dbconnection->prepare(
                                'UPDATE 
                                    tl_visitors_pages 
                                SET 
                                    visitors_page_hit = :vpagehit,
                                    visitors_page_visit = :vpagevis
                                WHERE 
                                    id = :vid
                                ')
                            ;
                $stmt->bindValue('vpagehit', $visitors_page_hits  , \PDO::PARAM_INT);
                $stmt->bindValue('vpagevis', $visitors_page_visits, \PDO::PARAM_INT);
                $stmt->bindValue('vid', $objPageHitVisitResult->id, \PDO::PARAM_INT);
                $stmt->execute();
    	    }
	    }
	    //Page Counter End

	    if ($objVisitIP->rowCount() < 1) 
	    { //Browser Check wenn nicht geblockt
		    //Only counting if User Agent is set.
		    if (\strlen(\Contao\Environment::get('httpUserAgent'))>0) 
		    {
			    // Variante 3
				$ModuleVisitorBrowser3 = new ModuleVisitorBrowser3();
				$ModuleVisitorBrowser3->initBrowser(\Contao\Environment::get('httpUserAgent'), implode(",", \Contao\Environment::get('httpAcceptLanguage')));
				if ($ModuleVisitorBrowser3->getLang() === null) 
				{
    		    	System::getContainer()
    	                   ->get('monolog.logger.contao')
			    	        ->log(LogLevel::ERROR,
			    	              'ModuleVisitorBrowser3 Systemerror',
			    	              array('contao' => new ContaoContext('ModulVisitors', TL_ERROR)));
				} 
				else 
				{
					$arrBrowser['Browser']  = $ModuleVisitorBrowser3->getBrowser();
					$arrBrowser['Version']  = $ModuleVisitorBrowser3->getVersion();
					$arrBrowser['Platform'] = $ModuleVisitorBrowser3->getPlatformVersion();
					$arrBrowser['lang']     = $ModuleVisitorBrowser3->getLang();
				    //Anpassen an Version 1 zur Weiterverarbeitung
				    if ($arrBrowser['Browser'] == 'unknown') 
				    {
				    	$arrBrowser['Browser'] = 'Unknown';
				    }
				    if ($arrBrowser['Version'] == 'unknown') 
				    {
				    	$arrBrowser['brversion'] = $arrBrowser['Browser'];
				    } 
				    else 
				    {
				    	$arrBrowser['brversion'] = $arrBrowser['Browser'] . ' ' . $arrBrowser['Version'];
				    }
				    if ($arrBrowser['Platform'] == 'unknown') 
				    {
				    	$arrBrowser['Platform'] = 'Unknown';
				    }
				    //Debug if ( $arrBrowser['Platform'] == 'Unknown' || $arrBrowser['Platform'] == 'Mozilla' || $arrBrowser['Version'] == 'unknown' ) {
				    //Debug 	log_message("Unbekannter User Agent: ".$this->Environment->httpUserAgent."", 'unknown.log');
				    //Debug }
				    $objBrowserCounter = $dbconnection->prepare(
				                        'SELECT 
                                            id,
                                            visitors_counter
                                        FROM 
                                            tl_visitors_browser
                                        WHERE 
                                            vid = :vid 
                                            AND visitors_browser = :vbrowser
                                            AND visitors_os = :vos
                                            AND visitors_lang = :vlang
                                            ')
                                    ;
                    $objBrowserCounter->bindValue('vid', $vid                           , \PDO::PARAM_INT);
                    $objBrowserCounter->bindValue('vbrowser', $arrBrowser['brversion']  , \PDO::PARAM_STR);
                    $objBrowserCounter->bindValue('vos', $arrBrowser['Platform']        , \PDO::PARAM_STR);
                    $objBrowserCounter->bindValue('vlang', $arrBrowser['lang']          , \PDO::PARAM_STR);
                    $objBrowserCounter->execute();

				    //setzen
				    if ($objBrowserCounter->rowCount() < 1) 
				    {
				        // Insert
				        $arrSet = array
			            (
			                'vid'               => $vid,
			                'visitors_browser'  => $arrBrowser['brversion'], // version
			                'visitors_os'		=> $arrBrowser['Platform'],  // os
			                'visitors_lang'		=> $arrBrowser['lang'],
			                'visitors_counter'  => 1
                        );
                        $dbconnection->insert('tl_visitors_browser', $arrSet);
                        /*
					    \Database::getInstance()
					            ->prepare("INSERT INTO tl_visitors_browser %s")
                                ->set($arrSet)
                                ->execute();
                        */
				    } 
				    else 
				    {
                        //Update
                        $objBrowserCounterResult = $objBrowserCounter->fetch(\PDO::FETCH_OBJ);
				        $visitors_counter = $objBrowserCounterResult->visitors_counter +1;
                        // Update
                        $stmt = $dbconnection->prepare(
                                        'UPDATE 
                                            tl_visitors_browser 
                                        SET 
                                            visitors_counter = :vcounter
                                        WHERE 
                                            id = :vid
                                        ')
                                    ;
                        $stmt->bindValue('vcounter', $visitors_counter  , \PDO::PARAM_INT);
                        $stmt->bindValue('vid', $objBrowserCounterResult->id, \PDO::PARAM_INT);
                        $stmt->execute();
				    }
			    } // else von NULL
			} // if strlen
	    } //VisitIP numRows
    } //visitorCountUpdate
    
    protected function visitorCheckSearchEngine($vid)
	{
		$ModuleVisitorSearchEngine = new ModuleVisitorSearchEngine();
		$ModuleVisitorSearchEngine->checkEngines();
		$SearchEngine = $ModuleVisitorSearchEngine->getEngine();
		$Keywords     = $ModuleVisitorSearchEngine->getKeywords();
		if ($SearchEngine !== 'unknown') 
		{
			$this->_SE = true;
			if ($Keywords !== 'unknown') 
			{
				// Insert
		        $arrSet = array
		        (
		            'vid'                   => $vid,
		            'tstamp'                => time(),
		            'visitors_searchengine' => $SearchEngine,
		            'visitors_keywords'		=> $Keywords
                );
                $this->get('database_connection')
                        ->insert('tl_visitors_searchengines', $arrSet);
			    //\Database::getInstance()
			    //      ->prepare("INSERT INTO tl_visitors_searchengines %s")
                //      ->set($arrSet)
                //      ->execute();
			    // Delete old entries
                $CleanTime = mktime(0, 0, 0, (int) date("m")-3, (int) date("d"), (int) date("Y")); // Einträge >= 90 Tage werden gelöscht
                $stmt = $this->get('database_connection')
                            ->prepare(
                                'DELETE FROM 
                                    tl_visitors_searchengines
                                WHERE 
                                    vid = :vid AND tstamp < :tstamp
                                ')
                            ;
                $stmt->bindValue('vid', $vid, \PDO::PARAM_INT);
                $stmt->bindValue('tstamp', $CleanTime, \PDO::PARAM_INT);
                $stmt->execute(); 
			} //keywords
		} //searchengine
		//Debug log_message('visitorCheckSearchEngine $SearchEngine: ' . $SearchEngine,'debug.log');
	} //visitorCheckSearchEngine

	/**
	 * Check for Referrer
	 *
	 * @param integer $vid Visitors ID
	 */
	protected function visitorCheckReferrer($vid)
	{
		if ($this->_HitCounted === true) 
		{
			if ($this->_PF === false) 
			{
				$ModuleVisitorReferrer = new ModuleVisitorReferrer();
				$ModuleVisitorReferrer->checkReferrer();
				$ReferrerDNS = $ModuleVisitorReferrer->getReferrerDNS();
				$ReferrerFull= $ModuleVisitorReferrer->getReferrerFull();
				//Debug log_message('visitorCheckReferrer $ReferrerDNS:'.print_r($ReferrerDNS,true), 'debug.log');
				//Debug log_message('visitorCheckReferrer Host:'.print_r($this->ModuleVisitorReferrer->getHost(),true), 'debug.log');
				if ($ReferrerDNS != 'o' && $ReferrerDNS != 'w') 
				{ 	// not the own, not wrong
					// Insert
			        $arrSet = array
			        (
			            'vid'                   => $vid,
			            'tstamp'                => time(),
			            'visitors_referrer_dns' => $ReferrerDNS,
			            'visitors_referrer_full'=> $ReferrerFull
			        );
			        //Referrer setzen
                    //Debug log_message('visitorCheckReferrer Referrer setzen', 'debug.log');
                    $this->get('database_connection')
                            ->insert('tl_visitors_referrer', $arrSet);
			        //\Database::getInstance()
			        //      ->prepare("INSERT INTO tl_visitors_referrer %s")
                    //      ->set($arrSet)
                    //      ->execute();
				    // Delete old entries
                    $CleanTime = mktime(0, 0, 0, (int) date("m")-4, (int) date("d"), (int) date("Y")); // Einträge >= 120 Tage werden gelöscht

                    $stmt = $this->get('database_connection')
                                ->prepare(
                                    'DELETE FROM 
                                        tl_visitors_referrer
                                    WHERE 
                                        vid = :vid AND tstamp < :tstamp
                                    ')
                                ;
                    $stmt->bindValue('vid', $vid, \PDO::PARAM_INT);
                    $stmt->bindValue('tstamp', $CleanTime, \PDO::PARAM_INT);
                    $stmt->execute();        
		    	}
		    } //if PF
	    } //if VB
	} // visitorCheckReferrer
}
