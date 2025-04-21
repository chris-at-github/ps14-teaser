<?php

declare(strict_types=1);

namespace Ps14\Teaser\Controller;


use Ps14\Teaser\Domain\Model\Page;
use Ps14\Teaser\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Database\QueryGenerator as QueryGeneratorAlias;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
		$this->pageRepository->setQuerySettings(['respectStoragePage' => false]);
	}

	/**
	 * @param array $overwrite
	 * @return array
	 */
	protected function getDemand($overwrite = []) {
		$record = $this->request->getAttribute('currentContentObject')->data;
		$options = [
			'not' => []
		];

		if($this->settings['source'] === 'pages' && empty($this->settings['pages']) === false) {

			if($this->settings['pagesProcessing'] === 'subpages') {
				$options['parent'] = GeneralUtility::trimExplode(',', $this->settings['pages'], true);

			} else {
				$options['records'] = GeneralUtility::trimExplode(',', $this->settings['pages'], true);

				// bei Eingabe von festen IDs duerfen nur die IDs der Hauptsprache verwendet werden, Extbase kuemmert sich per
				// Overlay um die korrekte Uebersetzung
				$this->pageRepository->setQuerySettings(['respectSysLanguage' => false]);
			}
		} elseif($this->settings['source'] === 'categories' && empty($record['pages']) === false) {

			// sonst wuerde TYPO3 die deutsche und die englische Variante doppelt ausspielen
			// so werden direkt die englischen Datensaetze geladen
			$this->pageRepository->setQuerySettings(['respectSysLanguage' => true]);

			/** @var \TYPO3\CMS\Core\Domain\Repository\PageRepository $corePageRepository */
			$corePageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);
			$options['parent'] = $corePageRepository->getPageIdsRecursive(GeneralUtility::intExplode(',', $record['pages']), (int) $record['recursive']);
		}

		if($this->settings['source'] === 'categories' && empty($this->settings['categories']) === false) {

			// sonst wuerde TYPO3 die deutsche und die englische Variante doppelt ausspielen
			// so werden direkt die englischen Datensaetze geladen
			$this->pageRepository->setQuerySettings(['respectSysLanguage' => true]);

			$options['categories'] = GeneralUtility::trimExplode(',', $this->settings['categories'], true);
		}

		// eigene Seite ausschliessen
		$options['not']['records'] = [$GLOBALS['TSFE']->id];

		if((int) $GLOBALS['TSFE']->page['sys_language_uid'] !== 0 && empty($GLOBALS['TSFE']->page['_PAGES_OVERLAY_UID']) === false) {
			$options['not']['records'][] = $GLOBALS['TSFE']->page['_PAGES_OVERLAY_UID'];
		}

		// hide_nav Datensaetze per Default nicht anzeigen
		if((int) $this->settings['hiddenEnabled'] !== 1) {
			$options['hiddenEnabled'] = false;
		}

		return $options;
	}

	/**
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function indexAction() {
		$demand = $this->getDemand();
		$pages = $this->pageRepository->findAllByOption($demand);

		if($this->settings['source'] === 'pages' && $this->settings['pagesProcessing'] === 'pages') {
			$pages = \Ps14\Foundation\Utilities\ArrayUtility::sortByField($pages, $demand['records'], function($value) {
				if($value instanceof Page) {
					return $value->getUid();
				}

				return null;
			});
		}

		$this->view->assign('pages', $pages);
		$this->view->assign('record', $this->request->getAttribute('currentContentObject')->data);

		return $this->htmlResponse();
	}
}
