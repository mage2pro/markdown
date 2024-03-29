<?php
namespace Dfe\Markdown\Observer\Catalog\ControllerAction;
use Dfe\Markdown\DbRecord;
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
final class Postdispatch extends ControllerAction {
	/**
	 * 2015-11-04
	 * @override
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction::handleCustomValue()
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction::processPost()
	 * @param array(string => string|array) $post
	 */
	protected function handleCustomValue(array &$post, string $shortKey):void {DbRecord::save(
		$shortKey, $post["$shortKey{$this->suffix()}"]
	);}

	/**
	 * 2015-11-04
	 * @override
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction::suffix()
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction::processPost()
	 */
	protected function suffix():string {return Predispatch::MARKDOWN_SUFFIX;}
}


