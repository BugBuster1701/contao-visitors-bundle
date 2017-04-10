<?php

/**
 * Contao Open Source CMS, Copyright (C) 2005-2015 Leo Feyer
 * 
 * Modul Visitors Statistic Export 
 * 
 * @copyright	Glen Langer 2015 <http://contao.ninja>
 * @author      Glen Langer (BugBuster)
 * @package     VisitorsStatisticExport 
 * @license     LGPL 
 * @filesource
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace BugBuster\Visitors\Stat\Export; 

/**
 * Class VisitorsStatExport
 *
 * @copyright	Glen Langer 2015 <http://contao.ninja>
 * @author      Glen Langer (BugBuster)
 * @package     VisitorsStatisticExport 
 */
class VisitorsStatExport extends \System
{
    protected $catid  = 0;
    protected $format = 'xlsx';
    protected $BrowserAgent ='NOIE';
    protected $export_days = 0;
    
    /**
     */
    public function __construct()
    {
        parent::__construct();
        \System::loadLanguageFile('tl_visitors_stat_export');
        
        $this->format = \Input::post('visitors_export_format',true);
        $this->catid  = \Input::post('catid',true);
        $this->export_days = (int) \Input::post('visitors_export_days',true);
        
        if ($this->export_days <1) 
        {
        	$this->export_days = 1;
        }
        $_SESSION['VISITORS_EXPORT_DAYS'] = $this->export_days;
        
        //IE or other?
        $ua = \Environment::get('agent')->shorty;
        if ($ua == 'ie')
        {
            $this->BrowserAgent = 'IE';
        }
    }
    
    public function run()
    {
        switch ($this->format) 
        {
            case 'xlsx':
                $this->exportXLSX();
                break;
            case 'ods':
                $this->exportODS();
                break;
            case 'csv':
                $this->exportCSV();
                break;
        	default:
                break;
        }
        return;
    }
    
    
    protected function exportXLSX()
    {
        $objPHPExcel = $this->generateExportData(); 
        $objPHPExcel->getProperties()->setCreator("Contao Module visitors_statistic_export")
                                    ->setLastModifiedBy("Contao Module visitors_statistic_export")
                                    ->setTitle("Office 2007 XLSX Visitors Statistic Export")
                                    ->setSubject("Office 2007 XLSX Visitors Statistic Export")
                                    ->setDescription("Office 2007 XLSX Visitors Statistic Export");
                                    //->setKeywords("office 2007 openxml php")
                                    //->setCategory("Test result file");

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="visitors_statistic-export.xlsx"');
        header('Cache-Control: max-age=0');
        if ($this->BrowserAgent == 'IE')
        {
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
    
    protected function exportODS()
    {
        $objPHPExcel = $this->generateExportData();
        $objPHPExcel->getProperties()->setCreator("Contao Module visitors_statistic_export")
                                    ->setLastModifiedBy("Contao Module visitors_statistic_export")
                                    ->setTitle("Office 2007 ODS Visitors Statistic Export")
                                    ->setSubject("Office 2007 ODS Visitors Statistic Export")
                                    ->setDescription("Office 2007 ODS Visitors Statistic Export");
        //->setKeywords("office 2007 openxml php")
        //->setCategory("Test result file");
    
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
        header('Content-Disposition: attachment;filename="visitors_statistic-export.ods"');
        header('Cache-Control: max-age=0');
        if ($this->BrowserAgent == 'IE')
        {
            // If you're serving to IE 9, then the following may be needed
            //header('Cache-Control: max-age=1');
    
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
        }
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'OpenDocument');
        $objWriter->save('php://output');
        exit;
    }
    
       
    protected function exportCSV()
    {
        header('Content-Type: text/csv; charset=' . $GLOBALS['TL_CONFIG']['characterSet']);
        header('Content-Disposition: attachment;filename="visitors_statistic-export.utf8.csv"');
        header('Cache-Control: max-age=0');
        if ($this->BrowserAgent == 'IE')
        {
            // If you're serving to IE 9, then the following may be needed
            //header('Cache-Control: max-age=1');
        
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
        }
        
        $objPHPExcel = $this->generateExportData();
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV')
                    ->setDelimiter(',')
                    ->setEnclosure('"')
                    ->setLineEnding("\r\n")
                    ->setSheetIndex(0);
        $objWriter->save('php://output');
        $objWriter = null;
        unset($objWriter);
        exit;
    }
    
    
    protected function generateExportData()
    {
        $objStatistic = \Database::getInstance()
                            ->prepare("SELECT 
                                        tvc.title AS category_title, 
                                        tv.id AS visitors_id, 
                                        tv.visitors_name, 
                                        tv.published, 
                                        tvs.visitors_date, 
                                        tvs.visitors_visit, 
                                        tvs.visitors_hit
                                    FROM 
                                        tl_visitors AS tv
                                    LEFT JOIN 
                                        tl_visitors_counter AS tvs ON (tvs.vid=tv.id)
                                    LEFT JOIN 
                                        tl_visitors_category AS tvc ON (tvc.id=tv.pid)
                                    WHERE 
                                        tvc.id = ?
                                    ORDER BY tvc.title, tv.id, tvs.visitors_date")
                            ->execute($this->catid);
        
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle($GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_title']);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_category']);
        $objPHPExcel->getActiveSheet()->setCellValue('B1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_id']);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_name']);
        $objPHPExcel->getActiveSheet()->setCellValue('D1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_published']);
        $objPHPExcel->getActiveSheet()->setCellValue('E1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_date']);
        $objPHPExcel->getActiveSheet()->setCellValue('F1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_visits']);
        $objPHPExcel->getActiveSheet()->setCellValue('G1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_hits']);
        
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
        
        $row = 1;
        while ($objStatistic->next())
        {
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $objStatistic->category_title);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, $objStatistic->visitors_id);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $objStatistic->visitors_name);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $objStatistic->published=='' ? $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_no'] : $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_yes']);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row, date($GLOBALS['TL_CONFIG']['dateFormat'], strtotime($objStatistic->visitors_date)));
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row, $objStatistic->visitors_visit=='' ? '0' : $objStatistic->visitors_visit);
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row, $objStatistic->visitors_hit  =='' ? '0' : $objStatistic->visitors_hit);

            $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        $VisitorsID = $objStatistic->visitors_id;
        
        //Page Statistics
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle($GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_page_title']);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $GLOBALS['TL_LANG']['MSC']['tl_vivitors_stat']['page_alias']);
        $objPHPExcel->getActiveSheet()->setCellValue('B1', $GLOBALS['TL_LANG']['MSC']['tl_vivitors_stat']['page_language']);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_visits']);
        $objPHPExcel->getActiveSheet()->setCellValue('D1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_hits']);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
        
        $arrVisitorsPageVisitHits = \Visitors\ModuleVisitorStatPageCounter::getInstance()->generatePageVisitHitTopDays($VisitorsID,$this->export_days,false);
        $row = 1; 
        if (count($arrVisitorsPageVisitHits)>0 && $arrVisitorsPageVisitHits !== false) 
        {
            foreach ($arrVisitorsPageVisitHits as $arrVisitorsPageVisitHit) 
            {
                $row++;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$row, $arrVisitorsPageVisitHit['alias']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$row, $arrVisitorsPageVisitHit['lang']);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$row, $arrVisitorsPageVisitHit['visits']);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$row, $arrVisitorsPageVisitHit['hits']);
                
                $objPHPExcel->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            }
        }
        $objPHPExcel->setActiveSheetIndex(0);
        return $objPHPExcel;
    }

}

/**
 	// Check if zip class exists
// if (!class_exists($zipClass, FALSE)) {
// throw new PHPExcel_Reader_Exception($zipClass . " library is not enabled");
// }
 This allows the writing of Excel2007 files, even without ZipArchive enabled (it does require zlib), or when php_zip is one of the buggy PHP 5.2.6 or 5.2.8 versions
It can be enabled using PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);

 *  
*/
