<?php
namespace Dfe\Markdown\Observer;
use Dfe\Markdown\Setup\InstallSchema;
use Magento\Framework\Event\ObserverInterface;
/**
 * 2015-11-03
 * Обработка события «adminhtml_catalog_category_edit_prepare_form».
 * Цель обработки события — подстановка Markdown вместо HTML в поля редакторов
 * на административной странице товарного раздела.
 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Catalog/Block/Adminhtml/Category/Tab/Attributes.php#L112
		$form->addValues($this->getCategory()->getData());
		$this->_eventManager->dispatch(
			'adminhtml_catalog_category_edit_prepare_form', ['form' => $form]
		);
 */
/**
 * 2015-11-03
 * Обработка события «adminhtml_catalog_product_edit_prepare_form».
 * Цель обработки события — подстановка Markdown вместо HTML в поля редакторов
 * на административной странице товара.
 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Catalog/Block/Adminhtml/Product/Edit/Tab/Attributes.php#L124-L126
	$form->addValues($values);
	$form->setFieldNameSuffix('product');
	$this->_eventManager->dispatch(
		'adminhtml_catalog_product_edit_prepare_form',
		['form' => $form, 'layout' => $this->getLayout()]
	);
 */
class CatalogPrepareForm implements ObserverInterface {
	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @see \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes::_prepareForm()
	 * @see \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::_prepareForm()
	 * @param \Magento\Framework\Event\Observer $o
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $o) {
		if ($this->entityId() && \Dfe\Markdown\Settings::s()->enable()) {
			/** @var \Magento\Framework\Data\Form $form */
			$form = $o['form'];
			/** @var \Magento\Framework\Data\Form\Element\Fieldset $fieldset */
			$fieldset = $form->getElements()[0];
			foreach ($fieldset->getElements() as $element) {
				/** @var \Magento\Framework\Data\Form\Element\AbstractElement|\Dfe\Markdown\FormElement $element */
				if ($element instanceof \Dfe\Markdown\FormElement) {
					$this->loadMarkdown($element);
				}
			}
		}
	}

	/** @return int */
	private function entityId() {return df_request('id');}

	/** @return string */
	private function entityType() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = explode('_', df_action_name())[1];
		}
		return $this->{__METHOD__};
	}

	/** @return int */
	private function entityTypeId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_eav_config()->getEntityType('catalog_' . $this->entityType())->getId();
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2015-11-03
	 * @used-by \Dfe\Markdown\Observer\CatalogPrepareForm\Category::execute()
	 * @param \Dfe\Markdown\FormElement $element
	 * @return void
	 */
	private function loadMarkdown(\Dfe\Markdown\FormElement $element) {
		/** @var int $attributeId */
		$attributeId = df_fetch_one_int('eav_attribute', 'attribute_id', [
			/**
			 * 2015-11-03
			 * Здесь надо вызывать именно $element['name'], а не $element->getName(),
			 * потому что @see \Magento\Framework\Data\Form\Element\AbstractElement::getName()
			 * дополнительно обрабатывает имя, и мы получим «product[description]» вместе «description».
			 */
			'attribute_code' => $element['name']
			, 'entity_type_id' => $this->entityTypeId()
		]);
		/** @var int $valueId */
		$valueId = df_fetch_one_int("catalog_{$this->entityType()}_entity_text", 'value_id', [
			'attribute_id' => $attributeId
			, 'entity_id' => $this->entityId()
			, 'store_id' => df_store()->getId()
		]);
		if ($valueId) {
			/** @var string $markdown */
			/**
			 * @use InstallSchema::TABLE_CATEGORY
			 * @use InstallSchema::TABLE_PRODUCT
			 */
			$markdown = df_fetch_one("dfe_markdown_{$this->entityType()}", InstallSchema::F__MARKDOWN, [
				'value_id' => $valueId
			]);
			if ($markdown) {
				$element['value'] = $markdown;
			}
		}
	}
}


