<?php
namespace Dfe\Markdown\Observer;
use Magento\Framework\Event\ObserverInterface;
class ElementTypes implements ObserverInterface {
	/**
	 * 2015-10-23
	 * Обработка этого события позволяет нам подставить свой класс
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
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_getAdditionalElementTypes()
	 * https://github.com/magento/magento2/blob/0be91a56d050791ac0b67a47185d79df31e79329/app/code/Magento/Catalog/Block/Adminhtml/Product/Edit/Tab/Attributes.php#L151
		$response = new \Magento\Framework\DataObject();
		$response->setTypes([]);
		$this->_eventManager->dispatch(
			'adminhtml_catalog_product_edit_element_types', ['response' => $response]
		);
	 * https://3v4l.org/UidhW
	 * @param \Magento\Framework\Event\Observer $o
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			$o['response']['types'] = ['textarea' => 'Dfe\Markdown\FormElement'];
		}
	}
}


