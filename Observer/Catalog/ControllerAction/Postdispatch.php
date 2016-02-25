<?php
namespace Dfe\Markdown\Observer\Catalog\ControllerAction;
use Dfe\Markdown\CatalogAction\DbRecord;
use Dfe\Markdown\Observer\Catalog\ControllerAction;
/**
 * События:
 * 		controller_action_postdispatch_catalog_category_save
 * 		controller_action_postdispatch_catalog_product_save
 * Цель обработки этих событий —
 * сохранение содержимого Markdown после сохранения товара или раздела.
 *
 * @see \Magento\Framework\App\Action\Action::execute()
 * https://github.com/magento/magento2/blob/16dc76df41fac703b322cc0f9ab3dba43742bbed/lib/internal/Magento/Framework/App/Action/Action.php#L105-L108
 * https://github.com/magento/magento2/issues/2253
	$this->_eventManager->dispatch(
		'controller_action_postdispatch_' . $request->getFullActionName(),
		$eventParameters
	);
 */
class Postdispatch extends ControllerAction {
	/**
	 * 2015-11-04
	 * @override
	 * @see \Dfe\Markdown\Observer\ControllerAction\Catalog::handleCustomValue()
	 * @used-by \Dfe\Markdown\Observer\ControllerAction\Catalog::processPost()
	 * @param array(string => string|array) $post
	 * @param string $shortKey
	 * @return void
	 */
	protected function handleCustomValue(array &$post, $shortKey) {
		DbRecord::save($shortKey, $post[$shortKey . $this->suffix()]);
	}

	/**
	 * 2015-11-04
	 * @override
	 * @see \Dfe\Markdown\Observer\ControllerAction\Catalog::suffix()
	 * @used-by \Dfe\Markdown\Observer\ControllerAction\Catalog::processPost()
	 * @return string
	 */
	protected function suffix() {return Predispatch::MARKDOWN_SUFFIX;}
}


