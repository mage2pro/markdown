<?php
namespace Dfe\Markdown;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
# 2015-11-04
final class CatalogAction {
	/**
	 * 2015-11-04
	 * Раньше здесь стоял код df_request('id'), однако он ошибочен,
	 * потому что при сохранении нового объекта (например, товара)
	 * идентификатор этого нового объекта, очеидно, отсутствует в веб-адресе.
	 * Например, при сохранении нового товара веб-адрес может быть таким:
	 * http://site.com/admin/catalog/product/save/set/4/type/configurable/back/edit/active_tab/product-details/
	 *
	 * Новый алгоритм основан на использовании реестра.
	 * @see \Magento\Catalog\Controller\Adminhtml\Product\Builder::build()
	 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/app/code/Magento/Catalog/Controller/Adminhtml/Product/Builder.php#L88-L89
	 *	$this->registry->register('product', $product);
	 *	$this->registry->register('current_product', $product);
	 *
	 * @see \Magento\Catalog\Controller\Adminhtml\Category::_initCategory()
	 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/app/code/Magento/Catalog/Controller/Adminhtml/Category.php#L50-L51
		$this->_objectManager->get('Magento\Framework\Registry')->register('category', $category);
		$this->_objectManager->get('Magento\Framework\Registry')->register('current_category', $category);
	 *
	 * @used-by \Dfe\Markdown\Observer\Catalog\PrepareForm::execute()
	 * @used-by \Dfe\Markdown\DbRecord::valueId()
	 */
	static function entityId():int {
		$m = df_registry(self::entityType()); /** @var Category|Product $m */
		return $m->getId();
	}

	/**
	 * 2015-11-04
	 * @used-by self::entityId()
	 * @used-by \Dfe\Markdown\DbRecord::__construct()
	 */
	static function entityType():string {return dfcf(function() {return explode('_', df_action_name())[1];});}
}