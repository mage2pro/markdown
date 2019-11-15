<?php
namespace Dfe\Markdown;
use Magento\Catalog\Model\Product as P;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
/**
 * 2016-02-25
 * https://mage2.pro/tags/ui-component-data-modifier
 */
class Modifier extends AbstractModifier {
	/**
	 * 2016-02-25
	 * @override
	 * @see \Magento\Ui\DataProvider\Modifier\ModifierInterface::modifyData()
	 * https://github.com/magento/magento2/blob/e0ed4bad/app/code/Magento/Ui/DataProvider/Modifier/ModifierInterface.php#L13-L17
	 * @param array(string => mixed) $data
	 * @return array(string => mixed) $data
	 */
	function modifyData(array $data) {
		/**
		 * 2018-09-27
		 * 1) Magento 2.3 calls the `modifyData` method on the backend product list,
		 * and it leads to the error «The product wasn't registered»:
		 * https://github.com/mage2pro/markdown/issues/2
		 * @see \Magento\Catalog\Model\Locator\RegistryLocator::getProduct()
		 * 2) `df_action_is('catalog_product_index')` does not help here,
		 * because the `modifyData` method is called via AJAX too, and the action name is `mui_index_render`.
		 * "Magento 2.3: «Something went wrong with processing current custom view
		 * and filters have been reset to its original state. Please edit filters then click apply.":
		 * https://github.com/mage2pro/markdown/issues/3
		 */
		$p = df_product_current(); /** @var P|null $p */
		return !$p || !Settings::s()->enable() ? $data : array_replace_recursive($data, [$p->getId() => [
			self::DATA_SOURCE_DEFAULT => df_clean([
				'description' => DbRecord::load('description')
				,'short_description' => DbRecord::load('short_description')
			])
		]]);
	}

	/**
	 * 2016-02-25
	 * @override
	 * @see \Magento\Ui\DataProvider\Modifier\ModifierInterface::modifyMeta()
	 * https://github.com/magento/magento2/blob/e0ed4bad/app/code/Magento/Ui/DataProvider/Modifier/ModifierInterface.php#L19-L23
	 * @param array(string => mixed) $meta
	 * @return array(string => mixed) $meta
	 */
	function modifyMeta(array $meta) {return $meta;}
}


