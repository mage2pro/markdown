<?php
namespace Dfe\Markdown\Observer\Catalog;
use Dfe\Markdown\CatalogAction;
use Dfe\Markdown\DbRecord;
use Dfe\Markdown\FormElement as FE;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement as AE;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
/**
 * 2015-11-03
 * Обработка события «adminhtml_catalog_category_edit_prepare_form».
 * Цель обработки события — подстановка Markdown вместо HTML в поля редакторов
 * на административной странице товарного раздела.
 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Catalog
/Block/Adminhtml/Category/Tab/Attributes.php#L112
 *		$form->addValues($this->getCategory()->getData());
 *		$this->_eventManager->dispatch(
 *			'adminhtml_catalog_category_edit_prepare_form', ['form' => $form]
 *		);
 */
/**
 * 2015-11-03
 * Обработка события «adminhtml_catalog_product_edit_prepare_form».
 * Цель обработки события — подстановка Markdown вместо HTML в поля редакторов
 * на административной странице товара.
 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Catalog/Block/Adminhtml/Product/Edit/Tab/Attributes.php#L124-L126
 *	$form->addValues($values);
 *	$form->setFieldNameSuffix('product');
 *	$this->_eventManager->dispatch(
 *		'adminhtml_catalog_product_edit_prepare_form',
 *		['form' => $form, 'layout' => $this->getLayout()]
 *	);
 */
final class PrepareForm implements ObserverInterface {
	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @see \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes::_prepareForm()
	 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm()
	 */
	function execute(O $o):void {
		if (CatalogAction::entityId() && \Dfe\Markdown\Settings::s()->enable()) {
			$form = $o['form']; /** @var Form $form */
			/** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
			$fieldset = $form->getElements()[0];
			foreach ($fieldset->getElements() as $e) {/** @var AE|FE $e */
				if ($e instanceof FE) {
					/**
					 * 2015-11-03
					 * Здесь надо вызывать именно $element['name'], а не $element->getName(), потому что
					 * @see \Magento\Framework\Data\Form\Element\AbstractElement::getName() дополнительно обрабатывает имя,
					 * и мы получим «product[description]» вместе «description».
					 */
					$e['value'] = DbRecord::load($e['name']);
				}
			}
		}
	}
}


