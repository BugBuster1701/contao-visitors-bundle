<?php

declare(strict_types=1);

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace BugBuster\VisitorsBundle\Classes;

use Contao\Date;
use Contao\Input;
use Contao\PageModel;
use Contao\System;
use Doctrine\DBAL\Connection;

class VisitorCalculator
{
    public const PAGE_TYPE_NORMAL = 0; // 0   = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs

    public const PAGE_TYPE_NEWS = 1; // 1   = Nachrichten/News

    public const PAGE_TYPE_FAQ = 2; // 2   = FAQ

    public const PAGE_TYPE_ISOTOPE = 3; // 3   = Isotope

    public const PAGE_TYPE_EVENTS = 4; // 4   = Events

    public const PAGE_TYPE_FORBIDDEN = 403; // 403 = Forbidden Page

    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    // public function __invoke(): void
    // {
    //     $records = $this->connection->fetchAllAssociative("SELECT * FROM tl_foobar");

    //     // …
    // }

    public function getVisitorValues(array $rowBasics, int $visitors_category, $objPage, int $pagetype = 0, int $specialid = 0)
    {
        $VisitorsStartDate = false;
        $VisitorsAverageVisits = false;
        $VisitorsAverageVisitsValue = 0;

        $boolSeparator = 1 === (int) $rowBasics['visitors_thousands_separator'] ? true : false;

        if (\strlen($rowBasics['visitors_startdate'])) {
            $VisitorsStartDate = Date::parse($objPage->dateFormat, $rowBasics['visitors_startdate']);
        }

        if ($rowBasics['visitors_average']) {
            $VisitorsAverageVisits = true;
            $VisitorsAverageVisitsValue = $this->getAverageVisits($rowBasics['id'], $boolSeparator);
        }

        // if (!isset($GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'])) {
        //     $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'] = '';
        // }

        $arrVisitors[] = [
            // 'VisitorsNameLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'],
            'VisitorsName' => trim($rowBasics['visitors_name']),

            'VisitorsKatID' => $visitors_category,
            // 'VisitorsCounting' => $counting,
            'VisitorsStartDate' => $VisitorsStartDate, // false|value - ugly - i know

            // 'AverageVisitsLegend' => $GLOBALS['TL_LANG']['visitors']['AverageVisitsLegend'],
            'AverageVisits' => $VisitorsAverageVisits, // bool
            'AverageVisitsValue' => $VisitorsAverageVisitsValue,

            // 'VisitorsOnlineCountLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsOnlineCountLegend'],
            'VisitorsOnlineCountValue' => $this->getVisitorsOnlineCount($rowBasics['id'], $boolSeparator),

            // 'VisitorsStartDateLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsStartDateLegend'],
            'VisitorsStartDateValue' => $this->getVisitorsStartDate($rowBasics['visitors_startdate'], $objPage),

            // 'TotalVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TotalVisitCountLegend'],
            'TotalVisitCountValue' => $this->getTotalVisitCount($rowBasics, $boolSeparator),

            // 'TotalHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TotalHitCountLegend'],
            'TotalHitCountValue' => $this->getTotalHitCount($rowBasics, $boolSeparator),

            // 'TodayVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TodayVisitCountLegend'],
            'TodayVisitCountValue' => $this->getTodaysVisitCount($rowBasics, $boolSeparator),

            // 'TodayHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['TodayHitCountLegend'],
            'TodayHitCountValue' => $this->getTodaysHitCount($rowBasics, $boolSeparator),

            // 'YesterdayVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['YesterdayVisitCountLegend'],
            'YesterdayVisitCountValue' => $this->getYesterdayVisitCount($rowBasics, $boolSeparator),

            // 'YesterdayHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['YesterdayHitCountLegend'],
            'YesterdayHitCountValue' => $this->getYesterdayHitCount($rowBasics, $boolSeparator),

            // 'PageHitCountLegend' => $GLOBALS['TL_LANG']['visitors']['PageHitCountLegend'],
            'PageHitCountValue' => $this->getPageHits($rowBasics, $boolSeparator, $objPage, $pagetype, $specialid),
        ];

        return $arrVisitors;
    }

    protected function getAverageVisits($VisitorsId, $boolSeparator)
    {
        $VisitorsAverageVisits = 0;
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date('m'), (int) date('d') - 1, (int) date('Y')));

        $stmt = $this->connection->prepare('SELECT
                        SUM(visitors_visit) AS SUMV,
                        MIN( visitors_date) AS MINDAY
                    FROM
                        tl_visitors_counter
                    WHERE
                        vid = :vid AND visitors_date < :vdate

                    ');

        $stmt->bindValue('vid', $VisitorsId, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', $today, \PDO::PARAM_STR);
        $resultSet = $stmt->executeQuery();

        if ($resultSet->rowCount() > 0) {
            $rowBasicsAverageCount = $resultSet->fetchAssociative();
            if (null !== $rowBasicsAverageCount['SUMV']) {
                $tmpTotalDays = floor((strtotime($yesterday) - strtotime($rowBasicsAverageCount['MINDAY'])) / 60 / 60 / 24);
                $VisitorsAverageVisitCount = null === $rowBasicsAverageCount['SUMV'] ? 0 : (int) $rowBasicsAverageCount['SUMV'];
                if ($tmpTotalDays > 0) {
                    $VisitorsAverageVisits = round($VisitorsAverageVisitCount / $tmpTotalDays, 0);
                }
            }
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsAverageVisits, 0) : $VisitorsAverageVisits;
    }

    protected function getVisitorsOnlineCount($VisitorsId, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Visitor ID: '.$VisitorsId);
        $stmt = $this->connection->prepare('SELECT
                        COUNT(id) AS VOC
                    FROM
                        tl_visitors_blocker
                    WHERE
                        vid = :vid AND visitors_type = :vtype
                    ');

        $stmt->bindValue('vid', $VisitorsId, \PDO::PARAM_INT);
        $stmt->bindValue('vtype', 'v', \PDO::PARAM_STR);
        $resultSet = $stmt->executeQuery();

        $objVisitorsOnlineCount = $resultSet->fetchAssociative();
        $VisitorsOnlineCount = null === $objVisitorsOnlineCount['VOC'] ? 0 : $objVisitorsOnlineCount['VOC'];

        return $boolSeparator ? System::getFormattedNumber($VisitorsOnlineCount, 0) : $VisitorsOnlineCount;
    }

    protected function getVisitorsStartDate($VisitorsStartdate, $objPage)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Visitor Startdate: '.$VisitorsStartdate);
        if (\strlen($VisitorsStartdate)) {
            $VisitorsStartDate = Date::parse($objPage->dateFormat, $VisitorsStartdate);
        } else {
            $VisitorsStartDate = '';
        }

        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Visitor Startdate: '.$VisitorsStartDate);
        return $VisitorsStartDate;
    }

    protected function getTotalVisitCount($objVisitors, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id']);
        $stmt = $this->connection->prepare('SELECT
                        SUM(visitors_visit) AS SUMV
                    FROM
                        tl_visitors_counter
                    WHERE
                        vid = :vid
                    ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        $VisitorsTotalVisitCount = $objVisitors['visitors_visit_start']; // Startwert
        if ($resultSet->rowCount() > 0) {
            $objVisitorsTotalCount = $resultSet->fetchAssociative();
            $VisitorsTotalVisitCount += null === $objVisitorsTotalCount['SUMV'] ? 0 : $objVisitorsTotalCount['SUMV'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsTotalVisitCount, 0) : $VisitorsTotalVisitCount;
    }

    protected function getTotalHitCount($objVisitors, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id']);
        $stmt = $this->connection->prepare('SELECT
                            SUM(visitors_hit) AS SUMH
                        FROM
                            tl_visitors_counter
                        WHERE
                            vid = :vid
                        ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        $VisitorsTotalHitCount = $objVisitors['visitors_hit_start']; // Startwert
        if ($resultSet->rowCount() > 0) {
            $objVisitorsTotalCount = $resultSet->fetchAssociative();
            $VisitorsTotalHitCount += null === $objVisitorsTotalCount['SUMH'] ? 0 : $objVisitorsTotalCount['SUMH'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsTotalHitCount, 0) : $VisitorsTotalHitCount;
    }

    protected function getTodaysVisitCount($objVisitors, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id']);
        $stmt = $this->connection->prepare('SELECT
                            visitors_visit
                        FROM
                            tl_visitors_counter
                        WHERE
                            vid = :vid AND visitors_date = :vdate
                        ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d'), \PDO::PARAM_STR);
        $resultSet = $stmt->executeQuery();

        $VisitorsTodaysVisitCount = 0;
        if ($resultSet->rowCount() > 0) {
            $objVisitorsTodaysCount = $resultSet->fetchAssociative();
            $VisitorsTodaysVisitCount = null === $objVisitorsTodaysCount['visitors_visit'] ? 0 : $objVisitorsTodaysCount['visitors_visit'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsTodaysVisitCount, 0) : $VisitorsTodaysVisitCount;
    }

    protected function getTodaysHitCount($objVisitors, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id']);
        $stmt = $this->connection->prepare('SELECT
                            visitors_hit
                        FROM
                            tl_visitors_counter
                        WHERE
                            vid = :vid AND visitors_date = :vdate
                        ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d'), \PDO::PARAM_STR);
        $resultSet = $stmt->executeQuery();

        $VisitorsTodaysHitCount = 0;
        if ($resultSet->rowCount() > 0) {
            $objVisitorsTodaysCount = $resultSet->fetchAssociative();
            $VisitorsTodaysHitCount = null === $objVisitorsTodaysCount['visitors_hit'] ? 0 : $objVisitorsTodaysCount['visitors_hit'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsTodaysHitCount, 0) : $VisitorsTodaysHitCount;
    }

    protected function getYesterdayVisitCount($objVisitors, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id']);
        $stmt = $this->connection->prepare('SELECT
                            visitors_visit
                        FROM
                            tl_visitors_counter
                        WHERE
                            vid = :vid AND visitors_date = :vdate
                        ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d', strtotime('-1 days')), \PDO::PARAM_STR);
        $resultSet = $stmt->executeQuery();

        $VisitorsYesterdayVisitCount = 0;
        if ($resultSet->rowCount() > 0) {
            $objVisitorsYesterdayCount = $resultSet->fetchAssociative();
            $VisitorsYesterdayVisitCount = null === $objVisitorsYesterdayCount['visitors_visit'] ? 0 : $objVisitorsYesterdayCount['visitors_visit'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsYesterdayVisitCount, 0) : $VisitorsYesterdayVisitCount;
    }

    protected function getYesterdayHitCount($objVisitors, $boolSeparator)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id']);
        $stmt = $this->connection->prepare('SELECT
                            visitors_hit
                        FROM
                            tl_visitors_counter
                        WHERE
                            vid = :vid AND visitors_date = :vdate
                        ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $stmt->bindValue('vdate', date('Y-m-d', strtotime('-1 days')), \PDO::PARAM_STR);
        $resultSet = $stmt->executeQuery();

        $VisitorsYesterdayHitCount = 0;
        if ($resultSet->rowCount() > 0) {
            $objVisitorsYesterdayCount = $resultSet->fetchAssociative();
            $VisitorsYesterdayHitCount = null === $objVisitorsYesterdayCount['visitors_hit'] ? 0 : $objVisitorsYesterdayCount['visitors_hit'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsYesterdayHitCount, 0) : $VisitorsYesterdayHitCount;
    }

    protected function getPageHits($objVisitors, $boolSeparator, $objPage, $pagetype = 0, $specialid = 0)
    {
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'objVisitors ID: '.$objVisitors['id'].' objPage ID:'.$objPage->id);
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Page ID: '.$objPage->id);

        // #80, bei Readerseite den Beitrags-Alias beachten
        // 0 = reale Seite / 404 / Reader ohne Parameter - Auflistung der News/FAQs
        // 1 = Nachrichten/News
        // 2 = FAQ
        // 3 = Isotope
        // 403 = Forbidden
        $objPageId = $objPage->id;
        // bei News/FAQ id des Beitrags ermitteln und $objPage->id ersetzen
        if (self::PAGE_TYPE_NORMAL < $pagetype) {
            $objPageId = $specialid;
        }
        // $objPageId = $this->visitorGetPageIdByType($objPage->id, $visitors_page_type, $objPage->alias);

        $stmt = $this->connection->prepare('SELECT
                            SUM(visitors_page_hit)   AS visitors_page_hits
                        FROM
                        tl_visitors_pages
                        WHERE
                            vid = :vid
                        AND
                            visitors_page_id = :vpageid
                        AND
                            visitors_page_type = :vpagetype
                        ');

        $stmt->bindValue('vid', $objVisitors['id'], \PDO::PARAM_INT);
        $stmt->bindValue('vpageid', $objPageId, \PDO::PARAM_INT);
        $stmt->bindValue('vpagetype', $pagetype, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();

        $VisitorsPageHits = 0;
        if ($resultSet->rowCount() > 0) {
            $objPageStatCount = $resultSet->fetchAssociative();
            $VisitorsPageHits = null === $objPageStatCount['visitors_page_hits'] ? 0 : $objPageStatCount['visitors_page_hits'];
        }

        return $boolSeparator ? System::getFormattedNumber($VisitorsPageHits, 0) : $VisitorsPageHits;
    }

    /**
     * Get Page-Type.
     *
     * @param object $objPage
     *
     * @return int 0 = reale Seite, 1 = News, 2 = FAQ, 403 = Forbidden
     */
    protected function visitorGetPageType($objPage)
    {
        $PageId = $objPage->id;
        // Return:
        // 0 = reale Seite / Reader ohne Parameter - Auflistung der News/FAQs
        // 1 = Nachrichten/News
        // 2 = FAQ
        // 3 = Isotope
        // 4 = Event/Calendar
        // 403 = Forbidden

        $page_type = self::PAGE_TYPE_NORMAL;

        if (1 === (int) $objPage->protected) {
            return self::PAGE_TYPE_FORBIDDEN;
            // protected Seite. user
            // $user = $this->container->get('security.helper')->getUser();

            // if (!$user instanceof FrontendUser) {
            //     $page_type = self::PAGE_TYPE_FORBIDDEN;
            //     // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '.$page_type);

            //     return $page_type;
            // }
        }

        // Set the item from the auto_item parameter
        // from class ModuleNewsReader#L57
        // if (!isset($_GET['items']) && \Contao\Config::get('useAutoItem') && isset($_GET['auto_item'])) {
        //     \Contao\Input::setGet('items', \Contao\Input::get('auto_item'));
        // }
        // if (!\Contao\Input::get('items')) {
        //     ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '.$page_type);

        //     return $page_type;
        // }

        // if (isset($_GET['auto_item']) && '' !== $_GET['auto_item']) {
        //     \Contao\Input::setGet('auto_item', $_GET['auto_item']);
        // }
        // if (!\Contao\Input::get('auto_item')) {
        //     ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '.$page_type);

        //     return $page_type;
        // }

        $dbconnection = $this->connection;

        // News Table exists?
        if ($dbconnection->createSchemaManager()->tablesExist(['tl_news'])) {
            // News Reader?
            $stmt = $dbconnection->prepare('SELECT id
                        FROM tl_news_archive
                        WHERE jumpTo = :jumpto
                        LIMIT 1
                        ');
            $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                // News Reader
                return self::PAGE_TYPE_NEWS;
            }
        }

        // FAQ Table exists?
        if ($dbconnection->createSchemaManager()->tablesExist(['tl_faq_category'])) {
            // FAQ Reader?
            $stmt = $dbconnection->prepare('SELECT id
                        FROM tl_faq_category
                        WHERE jumpTo = :jumpto
                        LIMIT 1
                        ');
            $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                // FAQ Reader
                return self::PAGE_TYPE_FAQ;
            }
        }

        // Isotope Table tl_iso_product exists?
        if ($dbconnection->createSchemaManager()->tablesExist(['tl_iso_product'])) {
            $strAlias = Input::get('items');
            // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Get items: '.print_r($strAlias, true));

            $stmt = $dbconnection->prepare('SELECT id
                        FROM tl_iso_product
                        WHERE alias = :alias
                        LIMIT 1
                        ');
            $stmt->bindValue('alias', $strAlias, \PDO::PARAM_STR);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                // Isotope Reader
                return self::PAGE_TYPE_ISOTOPE;
            }
        }

        // Events Table exists?
        if ($dbconnection->createSchemaManager()->tablesExist(['tl_calendar'])) {
            // Events Reader?
            $stmt = $dbconnection->prepare('SELECT id
                        FROM tl_calendar
                        WHERE jumpTo = :jumpto
                        LIMIT 1
                        ');
            $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                // Events Reader
                return self::PAGE_TYPE_EVENTS;
            }
        }

        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '.$page_type);

        return $page_type;
    }

    /**
     * Get Page-ID by Page-Type.
     *
     * @param int    $PageId
     * @param int    $PageType
     * @param string $PageAlias
     *
     * @return int
     */
    protected function visitorGetPageIdByType($PageId, $PageType, $PageAlias)
    {
        if (self::PAGE_TYPE_NORMAL === $PageType) {
            // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNormal: '.$PageId);

            return $PageId;
        }

        if (self::PAGE_TYPE_FORBIDDEN === $PageType) {
            // Page ID von der 403 Seite ermitteln - nicht mehr
            // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNormal over 403: '.$PageId);

            return $PageId;
        }

        // Reader mit Parameter oder ohne?
        $uri = $_SERVER['REQUEST_URI']; // /news/james-wilson-returns.html
        $alias = '';
        $urlSuffix = '';
        // steht suffix (html) am Ende?
        $container = System::getContainer();
        $objRequest = $container->get('request_stack')->getCurrentRequest();
        if (null !== $objRequest && ($objPage = $objRequest->attributes->get('pageModel')) instanceof PageModel) {
            $urlSuffix = (string) $objPage->urlSuffix;
        }
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Auto Item: '.($_GET['auto_item'] ?? '--'));
        // wenn gleich dann hat Url ein Suffix wie .html, wenn ungleich dann Suffix ''
        if (substr($uri, -\strlen($urlSuffix)) === $urlSuffix) {
            // Suffix vorhanden
            // Alias nehmen
            $alias = substr($uri, strrpos($uri, '/') + 1, -\strlen($urlSuffix));
            if (false === $alias) {
                // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdReaderSelf: '.$PageId);

                return $PageId; // kein Parameter, Readerseite selbst
            }
        } else {
            // Suffix nicht vorhanden
            $alias = substr($uri, strrpos($uri, '/') + 1);
        }
        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Alias: '.$alias.' Suffix: '.$urlSuffix);

        $dbconnection = $this->connection;

        if (self::PAGE_TYPE_NEWS === $PageType) {
            // alias = news-details - Reader direkt = wenn auto_item leer
            if (!isset($_GET['auto_item'])) {
                $stmt = $dbconnection->prepare('SELECT id
                            FROM tl_news_archive
                            WHERE jumpTo = :jumpto
                            LIMIT 1
                            ');
                $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
                $resultSet = $stmt->executeQuery();

                if ($resultSet->rowCount() > 0) {
                    // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdReaderSelf: '.$PageId);

                    return $PageId;
                }
            }

            // alias = james-wilson-returns
            $stmt = $dbconnection->prepare('SELECT t.id
                        FROM tl_news t
                        JOIN tl_news_archive r ON t.pid = r.id
                        WHERE
                            t.alias = :alias
                            AND
                            r.jumpTo = :jumpTo
                        LIMIT 1
                        ');
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->bindValue('jumpTo', $PageId, \PDO::PARAM_STR);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                $objNews = $resultSet->fetchAssociative();
                // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdNews: '.$objNews['id']);

                return $objNews['id'];
            }
        }
        if (self::PAGE_TYPE_FAQ === $PageType) {
            // Reader direkt?
            if (!isset($_GET['auto_item'])) {
                $stmt = $dbconnection->prepare('SELECT id
                    FROM tl_faq_category
                    WHERE jumpTo = :jumpto
                    LIMIT 1
                    ');
                $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
                $resultSet = $stmt->executeQuery();

                if ($resultSet->rowCount() > 0) {
                    // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdReaderSelf: '.$PageId);

                    return $PageId;
                }
            }
            // alias = are-there-exams-how-do-they-work
            $stmt = $dbconnection->prepare('SELECT t.id
                        FROM tl_faq t
                        JOIN tl_faq_category r ON t.pid = r.id
                        WHERE
                            t.alias = :alias
                            AND
                            r.jumpTo = :jumpTo
                        LIMIT 1
                        ');
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->bindValue('jumpTo', $PageId, \PDO::PARAM_STR);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                $objFaq = $resultSet->fetchAssociative();
                // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdFaq: '.$objFaq['id']);

                return $objFaq['id'];
            }
        }
        if (self::PAGE_TYPE_ISOTOPE === $PageType) {
            // alias = a-perfect-circle-thirteenth-step
            $stmt = $dbconnection->prepare('SELECT id
                        FROM tl_iso_product
                        WHERE alias = :alias
                        LIMIT 1
                        ');
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                $objIsotope = $resultSet->fetchAssociative();
                // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdIsotope: '.$objIsotope['id']);

                return $objIsotope['id'];
            }
        }
        if (self::PAGE_TYPE_EVENTS === $PageType) {
            // Events Reader?
            if (!isset($_GET['auto_item'])) {
                $stmt = $dbconnection->prepare('SELECT id
                            FROM tl_calendar
                            WHERE jumpTo = :jumpto
                            LIMIT 1
                            ');
                $stmt->bindValue('jumpto', $PageId, \PDO::PARAM_INT);
                $resultSet = $stmt->executeQuery();

                if ($resultSet->rowCount() > 0) {
                    // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageType: '.$PageId);

                    return $PageId;
                }
            }
            // alias = james-wilson-returns
            $stmt = $dbconnection->prepare('SELECT t.id
                        FROM tl_calendar_events t
                        JOIN tl_calendar r ON t.pid = r.id
                        WHERE
                            t.alias = :alias
                            AND
                            r.jumpTo = :jumpTo
                        LIMIT 1
                        ');
            $stmt->bindValue('alias', $alias, \PDO::PARAM_STR);
            $stmt->bindValue('jumpTo', $PageId, \PDO::PARAM_STR);
            $resultSet = $stmt->executeQuery();

            if ($resultSet->rowCount() > 0) {
                $objNews = $resultSet->fetchAssociative();
                // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'PageIdEvent: '.$objNews['id']);

                return $objNews['id'];
            }
        }

        // ModuleVisitorLog::writeLog(__METHOD__, __LINE__, 'Unknown PageType: '.$PageType);
    }
}
