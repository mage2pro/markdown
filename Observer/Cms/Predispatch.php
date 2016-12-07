<?php
namespace Dfe\Markdown\Observer\Cms;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
/**
 * 2015-11-02
 * События:
 * 		controller_action_predispatch_cms_block_save
 * 		controller_action_predispatch_cms_page_save
 * Цель обработки этоих событий — перетасовка содержимое полей:
 * в поле «content» подставляем HTML вместо Markdown (содержимое поля «content_html_compiled»),
 * а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
 *
 * @see \Magento\Framework\App\Action\Action::execute()
 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/lib/internal/Magento/Framework/App/Action/Action.php#L93-L96
	$this->_eventManager->dispatch(
		'controller_action_predispatch_' . $request->getFullActionName(),
		$eventParameters
	);
 */
class Predispatch implements ObserverInterface {
	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @param O $o
	 * @return void
	 */
	public function execute(O $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			/** @var \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http $request */
			$request = $o['request'];
			// Обратите внимание, что мы перетасовываем содержимое полей:
			// в поле «content» подставляем HTML вместо Markdown,
			// а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
			/**@var \Zend\Stdlib\ParametersInterface $post */
			$post = $request->getPost();
			/** @var string $html */
			$html = $post['content' . \Dfe\Markdown\FormElement::HTML_COMPILED];
			// 2015-11-03
			// Перетасовываем данные только при их наличии.
			// Мало ли что...
			if ($html) {
				$post[\Dfe\Markdown\Setup\UpgradeSchema::F__MARKDOWN] = $post['content'];
				$post['content'] = $html;
			}
		}
	}
}


