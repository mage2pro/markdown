<?php
namespace Dfe\Markdown\Observer\Catalog;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
/**
 * 2015-10-23
 * События:
 * 		adminhtml_catalog_category_edit_element_types
 * 		adminhtml_catalog_product_edit_element_types
 *
 * Обработка этих событий позволяет нам подставить свой класс
 * для обработки товарных свойств типа «textarea»
 * вместо стандартного класса @see \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg
 *
 * Обратите внимание, что аналогичное событие для товарных разделов пока отсутствует:
 * https://github.com/magento/magento2/issues/2165
 * Inconsistency:
 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_getAdditionalElementTypes()
 * fires the event «adminhtml_catalog_product_edit_element_types»
 * but @see \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes::_getAdditionalElementTypes()
 * does not fire a similar event.
 *
 * Пока что устранил этот дефект своим классом, перекрывающим стандартный:
 * @see \Df\Catalog\Block\Adminhtml\Category\Tab\Attributes::_getAdditionalElementTypes()
 *
 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_getAdditionalElementTypes()
 * https://github.com/magento/magento2/blob/0be91a56d050791ac0b67a47185d79df31e79329/app/code/Magento/Catalog/Block/Adminhtml/Product/Edit/Tab/Attributes.php#L151
 *	$response = new \Magento\Framework\DataObject();
 *	$response->setTypes([]);
 *	$this->_eventManager->dispatch(
 *		'adminhtml_catalog_product_edit_element_types', ['response' => $response]
 *	);
 * https://3v4l.org/UidhW
 */
final class ElementTypes implements ObserverInterface {
	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @param O $o
	 * @return void
	 */
	function execute(O $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			$o['response']['types'] = ['textarea' => \Dfe\Markdown\FormElement::class];
		}
	}
}


