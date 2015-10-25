<?php
namespace Dfe\Markdown;
use Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg;
use Magento\Framework\Data\Form\Element\Textarea;
/** @used-by \Dfe\Markdown\Observer\AdminhtmlCatalogProductEditElementTypes::execute() */
class FormElement extends Wysiwyg {
	/**
	 * 2015-10-24
	 * @override
	 * @see \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::getAfterElementHtml()
	 * @used-by \Magento\Framework\Data\Form\Element\Textarea::getElementHtml()
	 */
	public function getAfterElementHtml() {
		/** http://stackoverflow.com/a/8212262 */
		/** @var string $result */
		$result = Textarea::getAfterElementHtml();
		if ($this->getIsWysiwygEnabled()) {
			$result .= "";
		}
		return $result;
	}
}