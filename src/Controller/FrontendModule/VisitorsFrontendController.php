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

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Date;
use Contao\FrontendUser;
use Contao\ModuleModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LogLevel;

class VisitorsFrontendController extends AbstractFrontendModuleController
{
    protected $strTemplate = 'mod_visitors_fe_all';
    protected $useragent_filter = '';
    protected $visitors_category = false;

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

        $this->useragent_filter  = $model->visitors_useragent;
        $this->visitors_category = $model->visitors_categories;

        /** @var PageModel $objPage */
        global $objPage;

        if (!is_numeric($this->visitors_category))
        {
            $this->strTemplate = 'mod_visitors_error';
            $template = new \Contao\FrontendTemplate($this->strTemplate); 

            return $template->getResponse();
        }

        if ($this->strTemplate != $model->visitors_template && $model->visitors_template !='')
        {
            $this->strTemplate = $model->visitors_template;
            $template = new \Contao\FrontendTemplate($this->strTemplate);
        }

        if ($this->strTemplate == 'mod_visitors_fe_invisible')
        {
            // invisible, but counting!
            //@todo Aufruf Zählmethode
            $arrVisitors[] = array('VisitorsKatID' => $this->visitors_category);
            $template->visitors = $arrVisitors;

            return $template->getResponse();
        } 

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
                        LIMIT :limit');
        $stmt->bindValue('pid',$this->visitors_category, \PDO::PARAM_INT);
        $stmt->bindValue('published',1,\PDO::PARAM_INT);
        $stmt->bindValue('limit',1, \PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() < 1)
        {
            \Contao\System::getContainer()
			     ->get('monolog.logger.contao')
			     ->log(LogLevel::ERROR,
			           'VisitorsFrontendController User Error: no published counter found.',
                       array('contao' => new ContaoContext('VisitorsFrontendController getResponse ', TL_ERROR)));
                       
            $this->strTemplate = 'mod_visitors_error';
            $template = new \Contao\FrontendTemplate($this->strTemplate); 

            return $template->getResponse();
        }

        while (false !== ($objVisitors = $stmt->fetch(\PDO::FETCH_OBJ))) 
        {
            $VisitorsStartDate      = false;
            $VisitorsAverageVisits  = false;
            if (\strlen($objVisitors->visitors_startdate)) 
            {
                $VisitorsStartDate = Date::parse($objPage->dateFormat, $objVisitors->visitors_startdate);
            } 
            
            if ($objVisitors->visitors_average) 
            {
                $VisitorsAverageVisits = true; 
                $VisitorsAverageVisitsValue = $this->getAverageVisits($objVisitors->id);
            } 

            if (!isset($GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'])) 
            {
                $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'] = '';
            }

            $arrVisitors[] = array
            (
                'VisitorsName'        => trim($objVisitors->visitors_name),
                'VisitorsKatID'       => $this->visitors_category, //$this->visitors_categories[0],
                'VisitorsStartDate'   => $VisitorsStartDate, 
                'AverageVisits'       => $VisitorsAverageVisits, 
                'AverageVisitsValue'  => $VisitorsAverageVisitsValue,
                'VisitorsNameLegend'        => $GLOBALS['TL_LANG']['visitors']['VisitorsNameLegend'],
                'VisitorsOnlineCountLegend' => $GLOBALS['TL_LANG']['visitors']['VisitorsOnlineCountLegend'],
                'VisitorsStartDateLegend'   => $GLOBALS['TL_LANG']['visitors']['VisitorsStartDateLegend'],
                'TotalVisitCountLegend'     => $GLOBALS['TL_LANG']['visitors']['TotalVisitCountLegend'],
                'TotalHitCountLegend'       => $GLOBALS['TL_LANG']['visitors']['TotalHitCountLegend'],
                'TodayVisitCountLegend'     => $GLOBALS['TL_LANG']['visitors']['TodayVisitCountLegend'],
                'TodayHitCountLegend'       => $GLOBALS['TL_LANG']['visitors']['TodayHitCountLegend'],
                'AverageVisitsLegend'       => $GLOBALS['TL_LANG']['visitors']['AverageVisitsLegend'],
                'YesterdayHitCountLegend'   => $GLOBALS['TL_LANG']['visitors']['YesterdayHitCountLegend'],
                'YesterdayVisitCountLegend' => $GLOBALS['TL_LANG']['visitors']['YesterdayVisitCountLegend'],
                'PageHitCountLegend'        => $GLOBALS['TL_LANG']['visitors']['PageHitCountLegend']
            );

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

    protected function getAverageVisits($VisitorsId)
    {
        $VisitorsAverageVisits = 0;
        $today     = date('Y-m-d');
        $yesterday = date('Y-m-d', mktime(0, 0, 0, (int) date("m"), (int) date("d")-1, (int) date("Y")));

        $stmt = $this->get('database_connection')
                ->prepare(
                    'SELECT 
                        SUM(visitors_visit) AS SUMV, 
                        MIN( visitors_date) AS MINDAY
                    FROM 
                        tl_visitors_counter
                    WHERE 
                        vid = :vid AND visitors_date < :vdate

                    ');
        $stmt->bindValue('vid', $VisitorsId, \PDO::PARAM_INT);
        $stmt->bindValue('vdate', $today, \PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) 
        {
            $objVisitorsAverageCount = $stmt->fetch(\PDO::FETCH_OBJ);
            $tmpTotalDays = floor((strtotime($yesterday) - strtotime($objVisitorsAverageCount->MINDAY))/60/60/24);
            $VisitorsAverageVisitCount = ($objVisitorsAverageCount->SUMV === null) ? 0 : (int) $objVisitorsAverageCount->SUMV;
            if ($tmpTotalDays > 0) 
            {
                $VisitorsAverageVisits = round($VisitorsAverageVisitCount / $tmpTotalDays, 0);
            } 
        }

        return $VisitorsAverageVisits;
    }


}
