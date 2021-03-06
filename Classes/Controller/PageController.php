<?php

declare(strict_types=1);

namespace Ps14\Teaser\Controller;


use Ps14\Teaser\Domain\Model\Page;
use Ps14\Teaser\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Database\QueryGenerator as QueryGeneratorAlias;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * This file is part of the "Ps14 Teaser" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Christian Pschorr <pschorr.christian@gmail.com>
 */


/**
 * PageController
 */
class PageController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var PageRepository
	 */
	protected $pageRepository = null;

	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository) {
		$this->pageRepository = $pageRepository;
	}

	/**
	 * @param array $overwrite
	 * @return array
	 */
	protected function getDemand($overwrite = []) {
		$options = [
			'not' => []
		];

		if($this->settings['source'] === 'pages' && empty($this->settings['pages']) === false) {
			if($this->settings['pagesProcessing'] === 'subpages') {
				$options['parent'] = GeneralUtility::trimExplode(',', $this->settings['pages'], true);

			} else {
				$options['records'] = GeneralUtility::trimExplode(',', $this->settings['pages'], true);
			}
		} elseif($this->settings['source'] === 'categories' && empty($this->configurationManager->getContentObject()->data['pages']) === false) {
			$depth = (int) $this->configurationManager->getContentObject()->data['recursive'];
			$queryGenerator = GeneralUtility::makeInstance( QueryGeneratorAlias::class);

			$children = $queryGenerator->getTreeList($this->configurationManager->getContentObject()->data['pages'], $depth, 0, 1);
			$options['parent'] = GeneralUtility::intExplode(',', $children, true);
		}

		if($this->settings['source'] === 'categories' && empty($this->settings['categories']) === false) {
			$options['categories'] = GeneralUtility::trimExplode(',', $this->settings['categories'], true);
		}

		// eigene Seite ausschliessen
		$options['not']['records'] = [$GLOBALS['TSFE']->id];

		// hide_nav Datensaetze per Default nicht anzeigen
		if((int) $this->settings['hiddenEnabled'] !== 1) {
			$options['hiddenEnabled'] = false;
		}

		return $options;
	}

	/**
	 * @return null|void
	 */
	public function indexAction() {
		$demand = $this->getDemand();
		$pages = $this->pageRepository->findAll($this->getDemand());

		if($this->settings['source'] === 'pages' && $this->settings['pagesProcessing'] === 'pages') {
			$pages = \Ps\Xo\Utilities\GeneralUtility::sortIterableByField($pages, $demand['records'], function($value) {
				if($value instanceof Page) {
					return $value->getUid();
				}

				return null;
			});
		}

		//DebuggerUtility::var_dump($pages);

		$this->settings['xo'] = $this->objectManager->get(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class)->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
			'xo'
		);

		$this->view->assign('pages', $pages);
		$this->view->assign('settings', $this->settings);
		$this->view->assign('record', $this->configurationManager->getContentObject()->data);
	}
}
