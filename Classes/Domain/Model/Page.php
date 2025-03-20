<?php

declare(strict_types=1);

namespace Ps14\Teaser\Domain\Model;


use \Ps14\Foundation\Domain\Model\Category;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Typolink\PageLinkBuilder;

/**
 * This file is part of the "Ps14 Teaser" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2021 Christian Pschorr <pschorr.christian@gmail.com>
 */


/**
 * Page
 */
class Page extends \Ps14\Foundation\Domain\Model\Page {

	/**
	 * @var string
	 */
	protected $abstractLong = '';

	/**
	 * @var string
	 */
	protected $teaserTitle = '';

	/**
	 * @var string
	 */
	protected $teaserReadmore = '';

	/**
	 * @var array
	 */
	protected $badges = [];

	/**
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 * @TYPO3\CMS\Extbase\Annotation\ORM\Cascade("remove")
	 */
	protected $teaserMediaLarge = null;

	/**
	 * @var string
	 */
	protected $url = '';

	/**
	 * Returns the abstractLong
	 *
	 * @return string $abstractLong
	 */
	public function getAbstractLong() {
		return $this->abstractLong;
	}

	/**
	 * Sets the abstractLong
	 *
	 * @param string $abstractLong
	 * @return void
	 */
	public function setAbstractLong(string $abstractLong) {
		$this->abstractLong = $abstractLong;
	}

	/**
	 * @return array
	 */
	public function getBadges() {
		if(empty($this->badges) === true) {
			$settings = $this->getSettings();
			$badgeParentCategories = GeneralUtility::intExplode(',', $settings['badgeParentCategories']);

			/** @var Category $category */
			foreach($this->getCategories() as $category) {
				if($category->getParent() !== null && in_array($category->getParent()->getUid(), $badgeParentCategories) === true) {
					$this->badges[] = $category;
				}
			}
		}

		return $this->badges;
	}

	/**
	 * liefert die TypoScript Plugin Einstellungen
	 *
	 * @return object
	 */
	public function getSettings() {
		return GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::class)->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
			'Ps14Teaser'
		);
	}

	/**
	 * @return string
	 */
	public function getTeaserTitle(): string {
		if(empty($this->teaserTitle) === true) {
			return $this->title;
		}

		return $this->teaserTitle;
	}

	/**
	 * @param string $teaserTitle
	 */
	public function setTeaserTitle(string $teaserTitle): void {
		$this->teaserTitle = $teaserTitle;
	}

	/**
	 * @return boolean
	 */
	public function isLinkable(): bool {
		if($this->isNotLinked() === true) {
			return false;
		}

		return true;
	}

	public function getTeaserReadmore(): string {
		if(empty($this->teaserReadmore) === false) {
			return $this->teaserReadmore;
		}

		return $this->getTeaserTitle();
	}

	public function setTeaserReadmore(string $teaserReadmore): void {
		$this->teaserReadmore = $teaserReadmore;
	}

	public function getTeaserMediaLarge(): ?\TYPO3\CMS\Extbase\Domain\Model\FileReference {
		if($this->teaserMediaLarge === null) {
			return $this->getMedia();
		}

		return $this->teaserMediaLarge;
	}

	public function setTeaserMediaLarge(?\TYPO3\CMS\Extbase\Domain\Model\FileReference $teaserMediaLarge): void {
		$this->teaserMediaLarge = $teaserMediaLarge;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function setUrl(string $url): void {
		$this->url = $url;
	}

	public function getLink(): string {
		$contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);
		$pageLinkBuilder = GeneralUtility::makeInstance(PageLinkBuilder::class, $contentObject);

		if(empty($this->url) === false) {
			$link = $contentObject->typoLink_URL(['parameter' => $this->url]);

		} else {
			$link = $contentObject->typoLink_URL(['parameter' => $this->uid]);
		}

		return $link;
	}
}
