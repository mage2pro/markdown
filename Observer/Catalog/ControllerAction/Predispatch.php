<?php
namespace Dfe\Markdown\Observer\Catalog\ControllerAction;
use Dfe\Markdown\Observer\Catalog\ControllerAction;
/**
 * События:
 * 		controller_action_predispatch_catalog_category_save
 * 		controller_action_predispatch_catalog_product_save
 * Цель обработки этих событий —
 * перетасовка содержимое полей при сохранении товара или раздела:
 * в основное поле редактора (например: «product[description]»)
 * подставляем HTML вместо Markdown (содержимое поля «product[description_html_compiled]»),
 * а в поле «product[description_markdown]» —
 * прежнее содержимое поля «product[description]» (т.е. Markdown).
 *
 * @see \Magento\Framework\App\Action\Action::execute()
 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/lib/internal/Magento/Framework/App/Action/Action.php#L93-L96
	$this->_eventManager->dispatch(
		'controller_action_predispatch_' . $request->getFullActionName(),
		$eventParameters
	);
 */
class Predispatch extends ControllerAction {
	/**
	 * 2015-11-04
	 * @override
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction::handleCustomValue()
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction::processPost()
	 * @param array(string => string|array) $post
	 * @param string $shortKey
	 */
	protected function handleCustomValue(array &$post, $shortKey):void {
		/** @var string $keyHtml */
		$keyHtml = $shortKey . $this->suffix();
		/** @var string|null $html */
		$html = $post[$keyHtml];
		/** @var string $value */
		$value = $post[$shortKey];
		$post[$shortKey] = $html;
		$post[$shortKey . self::MARKDOWN_SUFFIX] = $value;
	}

	/**
	 * 2015-11-04
	 * @override
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction::suffix()
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction::processPost()
	 * @return string
	 */
	protected function suffix() {return \Dfe\Markdown\FormElement::HTML_COMPILED;}

	const MARKDOWN_SUFFIX = '_markdown';
}


