<?php
/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 3/5/19
 * Time: 10:28 AM
 * To change this template use File | Settings | File Templates.
 */
namespace SilverStripers\CompareSites\Command;


use SilverStripers\CompareSites\Fetch\FetchPages;
use SilverStripers\CompareSites\Fetch\FetchPagesFromSite;
use SilverStripers\CompareSites\Helper\Cache;
use SilverStripers\CompareSites\Report\ComparisonReport;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CompareCommand extends Command
{

	protected static $defaultName = 'compare';
	public static $against_base = null;
	public static $site_base = null;
	public static $path = '';

	protected function configure()
	{
		$this
			->addArgument('domain', InputArgument::REQUIRED, 'Domain your are testing')
			->addArgument('mirror_domain', InputArgument::REQUIRED, 'The domain you are testing the domain against')
			->addArgument('path', InputArgument::REQUIRED, 'Path to generate the report to')
			->addArgument('depth', InputArgument::OPTIONAL, 'Depth of links to check')
			->setDescription('Compare two sites.')
			->setHelp('php bin/console run')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$domain = $input->getArgument('domain');
		$mirrorDomain = $input->getArgument('mirror_domain');
		$path = $input->getArgument('path');
		$depth = $input->getArgument('depth') ? : null;

		Cache::set_domain($domain);
		Cache::set_mirror_domain($mirrorDomain);
		try {
			$folder = Cache::set_report_path($path);
		} catch (\Exception $e) {
			$output->writeln($e->getMessage());
			return;
		}




		$output->writeln([
			'Starting',
			'============',
			'Generating reports on ' . $folder
		]);

		$fetcher = new FetchPages($mirrorDomain, $output);
		$fetcher->setType(FetchPages::MIRROR)->setDepth($depth)->run();
		$fetcher = new FetchPagesFromSite($output);
		$fetcher->run();

		$report = new ComparisonReport();
		$path = $report->makeReport();

		$output->writeln('Your Report is saved at: ' . $path);


	}

}