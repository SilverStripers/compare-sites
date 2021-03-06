<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 12:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace SilverStripers\CompareSites\Report;


use BigV\ImageCompare;
use SilverStripers\CompareSites\Fetch\FetchPages;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Helper\CrawlPage;

class ComparisonReport extends Report
{

	function makeReport()
	{
		$errors = [];
		$notfounds = [];
		$warnings = [];
		$success = [];

		$cache = Cache::get_cache();
		foreach ($cache as $url => $item) {

			/**
			 * @var $againstCP CrawlPage
			 * @var $siteCP CrawlPage
			 */
			$siteCP = $item[FetchPages::SITE];
			$againstCP = $item[FetchPages::MIRROR];
			if($siteCP->getResponseCode() == 404 || $siteCP->getResponseCode() == -1) {
				$notfounds[$url] = $item;
			}
			else if($siteCP->getResponseCode() == 200) {
				$siteImgPath = $siteCP->generateImage();
				$againstImgPath = $againstCP->generateImage();
				$comparisonResult = 0;
				if(file_exists($siteImgPath) && file_exists($againstImgPath)) {
					$image = new ImageCompare();
					$comparisonResult = $image->compare($siteImgPath, $againstImgPath);
				}

				if($comparisonResult == 0 || $comparisonResult == '~0') {
					$success[$url] = $item;
				}
				else {
					$warnings[$url] = $item;
				}

			}
			else {
				$errors[] = $item;
			}
		}

		$reportPath = $this->getReportPath();
		$html = $this->render([
			'Errors' => $errors,
			'NotFounds' => $notfounds,
			'Warnings' => $warnings,
			'Success' => $success
		]);

		file_put_contents($reportPath, $html);
		return $reportPath;
	}





}