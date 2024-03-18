<?php

/*
 * This file is part of a BugBuster Contao Bundle.
 *
 * @copyright  Glen Langer 2024 <http://contao.ninja>
 * @author     Glen Langer (BugBuster)
 * @package    Contao Visitors Bundle
 * @link       https://github.com/BugBuster1701/contao-visitors-bundle
 *
 * @license    LGPL-3.0-or-later
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */

namespace BugBuster\Visitors\Stat\Export;

use BugBuster\Visitors\ModuleVisitorStatPageCounter;
use Contao\Config;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\System;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Class VisitorsStatExport
 *
 * @copyright	Glen Langer 2023 <http://contao.ninja>
 */
class VisitorsStatExport extends System
{
	protected $catid  = 0;

	protected $format = 'xlsx';

	protected $BrowserAgent ='NOIE';

	protected $export_days = 0;

	public function __construct()
	{
		parent::__construct();
		System::loadLanguageFile('tl_visitors_stat_export');

		$this->format = Input::post('visitors_export_format', true);
		$this->catid  = Input::post('catid', true);
		$this->export_days = (int) Input::post('visitors_export_days', true);

		if ($this->export_days <1)
		{
			$this->export_days = 1;
		}
		// $_SESSION['VISITORS_EXPORT_DAYS'] = $this->export_days;
		$container = System::getContainer();
		$request = $container->get('request_stack')->getCurrentRequest();
		$request->getSession()->set('VISITORS_EXPORT_DAYS', $this->export_days);

		// IE or other?
		$ua = Environment::get('agent')->shorty;
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
	}

	protected function exportXLSX()
	{
		$objVisitorExcel = $this->generateExportData();
		$objVisitorExcel->getProperties()->setCreator("Contao Module visitors_statistic_export")
									->setLastModifiedBy("Contao Module visitors_statistic_export")
									->setTitle("Office 2007 XLSX Visitors Statistic Export")
									->setSubject("Office 2007 XLSX Visitors Statistic Export")
									->setDescription("Office 2007 XLSX Visitors Statistic Export");
		// ->setKeywords("office 2007 openxml php")
		// ->setCategory("Test result file");

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="visitors_statistic-export.xlsx"');
		header('Cache-Control: max-age=0');
		if ($this->BrowserAgent == 'IE')
		{
			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
		}
		$objWriter = IOFactory::createWriter($objVisitorExcel, 'Xlsx');
		$objWriter->save('php://output');
		exit;
	}

	protected function exportODS()
	{
		$objVisitorODS = $this->generateExportData();
		$objVisitorODS->getProperties()->setCreator("Contao Module visitors_statistic_export")
									->setLastModifiedBy("Contao Module visitors_statistic_export")
									->setTitle("Office 2007 ODS Visitors Statistic Export")
									->setSubject("Office 2007 ODS Visitors Statistic Export")
									->setDescription("Office 2007 ODS Visitors Statistic Export");
		// ->setKeywords("office 2007 openxml php")
		// ->setCategory("Test result file");

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.oasis.opendocument.spreadsheet');
		header('Content-Disposition: attachment;filename="visitors_statistic-export.ods"');
		header('Cache-Control: max-age=0');
		if ($this->BrowserAgent == 'IE')
		{
			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
		}
		$objWriter = IOFactory::createWriter($objVisitorODS, 'Ods');
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
			// If you're serving to IE over SSL, then the following may be needed
			header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
			header ('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
			header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
			header ('Pragma: public'); // HTTP/1.0
		}

		$objVisitorCSV = $this->generateExportData();
		$objWriter = IOFactory::createWriter($objVisitorCSV, 'Csv')
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
		$objStatistic = Database::getInstance()
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

		$objVisitorExcel = new Spreadsheet();
		$objVisitorExcel->setActiveSheetIndex(0);
		$objVisitorExcel->getActiveSheet()->setTitle($GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_title']);
		$objVisitorExcel->getActiveSheet()->setCellValue('A1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_category']);
		$objVisitorExcel->getActiveSheet()->setCellValue('B1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_id']);
		$objVisitorExcel->getActiveSheet()->setCellValue('C1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_name']);
		$objVisitorExcel->getActiveSheet()->setCellValue('D1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_published']);
		$objVisitorExcel->getActiveSheet()->setCellValue('E1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_date']);
		$objVisitorExcel->getActiveSheet()->setCellValue('F1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_visits']);
		$objVisitorExcel->getActiveSheet()->setCellValue('G1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_hits']);

		$objVisitorExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);

		$objVisitorExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);

		$row = 1;

		while ($objStatistic->next())
		{
			$row++;
			$objVisitorExcel->getActiveSheet()->setCellValue('A' . $row, $objStatistic->category_title);
			$objVisitorExcel->getActiveSheet()->setCellValue('B' . $row, $objStatistic->visitors_id);
			$objVisitorExcel->getActiveSheet()->setCellValue('C' . $row, $objStatistic->visitors_name);
			$objVisitorExcel->getActiveSheet()->setCellValue('D' . $row, empty($objStatistic->published) ? $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_no'] : $GLOBALS['TL_LANG']['MSC']['tl_visitors_stat']['pub_yes']);
			$objVisitorExcel->getActiveSheet()->setCellValue('E' . $row, date(Config::get('dateFormat'), strtotime($objStatistic->visitors_date)));
			$objVisitorExcel->getActiveSheet()->setCellValue('F' . $row, empty($objStatistic->visitors_visit) ? '0' : $objStatistic->visitors_visit);
			$objVisitorExcel->getActiveSheet()->setCellValue('G' . $row, empty($objStatistic->visitors_hit) ? '0' : $objStatistic->visitors_hit);

			$objVisitorExcel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
			$objVisitorExcel->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
		}
		$VisitorsID = $objStatistic->visitors_id;

		// Page Statistics
		$objVisitorExcel->createSheet();
		$objVisitorExcel->setActiveSheetIndex(1);
		$objVisitorExcel->getActiveSheet()->setTitle($GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_page_title']);
		$objVisitorExcel->getActiveSheet()->setCellValue('A1', $GLOBALS['TL_LANG']['MSC']['tl_vivitors_stat']['page_alias']);
		$objVisitorExcel->getActiveSheet()->setCellValue('B1', $GLOBALS['TL_LANG']['MSC']['tl_vivitors_stat']['page_language']);
		$objVisitorExcel->getActiveSheet()->setCellValue('C1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_visits']);
		$objVisitorExcel->getActiveSheet()->setCellValue('D1', $GLOBALS['TL_LANG']['tl_visitors_stat_export']['export_field_hits']);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
		$objVisitorExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
		$objVisitorExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);

		$arrVisitorsPageVisitHits = ModuleVisitorStatPageCounter::getInstance()->generatePageVisitHitTopDays($VisitorsID, $this->export_days, false);
		$row = 1;
		if ($arrVisitorsPageVisitHits !== false && \count($arrVisitorsPageVisitHits)>0)
		{
			foreach ($arrVisitorsPageVisitHits as $arrVisitorsPageVisitHit)
			{
				$row++;
				$objVisitorExcel->getActiveSheet()->setCellValue('A' . $row, $arrVisitorsPageVisitHit['alias']);
				$objVisitorExcel->getActiveSheet()->setCellValue('B' . $row, $arrVisitorsPageVisitHit['lang']);
				$objVisitorExcel->getActiveSheet()->setCellValue('C' . $row, $arrVisitorsPageVisitHit['visits']);
				$objVisitorExcel->getActiveSheet()->setCellValue('D' . $row, $arrVisitorsPageVisitHit['hits']);

				$objVisitorExcel->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
			}
		}
		$objVisitorExcel->setActiveSheetIndex(0);

		return $objVisitorExcel;
	}
}

/*
 * // Check if zip class exists
 * // if (!class_exists($zipClass, FALSE)) {
 * // throw new \PhpOffice\PhpSpreadsheet\Reader\Exception($zipClass . " library is not enabled");
 * // }
 * This allows the writing of Excel2007 files, even without ZipArchive enabled (it does require zlib), or when php_zip is one of the buggy PHP 5.2.6 or 5.2.8 versions
 * It can be enabled using \PhpOffice\PhpSpreadsheet\Settings::setZipClass(\PhpOffice\PhpSpreadsheet\Settings::PCLZIP);
 */
