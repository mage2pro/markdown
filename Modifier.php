<?php
namespace Dfe\Markdown;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
# 2016-02-25 https://mage2.pro/tags/ui-component-data-modifier
/** @final Unable to use the PHP «final» keyword here because of the M2 code generation. */
class Modifier extends AbstractModifier {
	/**
	 * 2016-02-25
	 * @override
	 * @see \Magento\Ui\DataProvider\Modifier\ModifierInterface::modifyData()
	 * https://github.com/magento/magento2/blob/e0ed4bad/app/code/Magento/Ui/DataProvider/Modifier/ModifierInterface.php#L13-L17
	 * @param array(string => mixed) $v
	 * @return array(string => mixed)
	 */
	function modifyData(array $v):array {
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
		;
		return !($id = df_product_current_id() /** @var int|null $id */) || !Settings::s()->enable()
			? $v : array_replace_recursive($v, [$id => [
				self::DATA_SOURCE_DEFAULT => df_clean([
					'description' => DbRecord::load('description')
					, 'short_description' => DbRecord::load('short_description')
				])
			]])
		;
	}

	/**
	 * 2016-02-25
	 * @override
	 * @see \Magento\Ui\DataProvider\Modifier\ModifierInterface::modifyMeta()
	 * https://github.com/magento/magento2/blob/e0ed4bad/app/code/Magento/Ui/DataProvider/Modifier/ModifierInterface.php#L19-L23
	 * @used-by \Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider::getMeta():
	 *		public function getMeta() {
	 *			$meta = parent::getMeta();
	 *			foreach ($this->pool->getModifiersInstances() as $modifier) {
	 *				$meta = $modifier->modifyMeta($meta);
	 *			}
	 *			return $meta;
	 *		}
	 * https://github.com/magento/magento2/blob/2.4.7-beta1/app/code/Magento/Catalog/Ui/DataProvider/Product/Form/ProductDataProvider.php#L67-L77
	 * @param array(string => mixed) $v
	 * @return array(string => mixed)
	 */
	function modifyMeta(array $v):array {return $v;}
}


