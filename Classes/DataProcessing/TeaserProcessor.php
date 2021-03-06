<?php

namespace Ps14\Teaser\DataProcessing;

use Ps\EntityProduct\Domain\Model\Product;
use Ps\EntityProduct\Domain\Repository\ProductRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class TeaserProcessor extends \Ps\Xo\DataProcessing\ModuleProcessor implements DataProcessorInterface {

	/**
	 * @param ContentObjectRenderer $contentObject The data of the content element or page
	 * @param array $contentObjectConfiguration The configuration of Content Object
	 * @param array $processorConfiguration The configuration of this processor
	 * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
	 * @return array the processed data as key/value store
	 */
	public function process(ContentObjectRenderer $contentObject, array $contentObjectConfiguration, array $processorConfiguration, array $processedData) {

		if($processedData['data']['list_type'] !== 'teaser_frontend') {
			return parent::process($contentObject, $contentObjectConfiguration, $processorConfiguration, $processedData);
		}

		$flexform = $this->getFlexFormData($contentObject, $processorConfiguration, $processedData);
		if($flexform !== null) {
			$processedData['data']['frame_classes'] .= ' ce-teaser-frontend--' . $flexform['settings']['layout'];
		}

		return parent::process($contentObject, $contentObjectConfiguration, $processorConfiguration, $processedData);
	}
}