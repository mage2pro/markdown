<?php
namespace Dfe\Markdown;
use Dfe\Markdown\CatalogAction\DbRecord;
use Dfe\Markdown\Settings;
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
	public function modifyData(array $data) {return
		!Settings::s()->enable() ? $data : array_replace_recursive($data, [
			df_catalog_locator()->getProduct()->getId() => [
				self::DATA_SOURCE_DEFAULT => df_clean([
					'description' => DbRecord::load('description')
					,'short_description' => DbRecord::load('short_description')
				])
			]
		])
	;}

	/**
	 * 2016-02-25
	 * @override
	 * @see \Magento\Ui\DataProvider\Modifier\ModifierInterface::modifyMeta()
	 * https://github.com/magento/magento2/blob/e0ed4bad/app/code/Magento/Ui/DataProvider/Modifier/ModifierInterface.php#L19-L23
	 * @param array(string => mixed) $meta
	 * @return array(string => mixed) $meta
	 */
	public function modifyMeta(array $meta) {return $meta;}
}


