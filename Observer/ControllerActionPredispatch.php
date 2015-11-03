<?php
namespace Dfe\Markdown\Observer;
use Magento\Framework\Event\ObserverInterface;
class ControllerActionPredispatch implements ObserverInterface {
	/**
	 * 2015-11-02
	 * Цель обработки этого события — перетасовка содержимое полей:
	 * в поле «content» подставляем HTML вместо Markdown (содержимое поля «content_html_compiled»),
	 * а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/lib/internal/Magento/Framework/App/Action/Action.php#L93-L96
		$this->_eventManager->dispatch(
			'controller_action_predispatch_' . $request->getFullActionName(),
			$eventParameters
		);
	 * @param \Magento\Framework\Event\Observer $o
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			/** @var \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http $request */
			$request = $o['request'];
			// Обратите внимание, что мы перетасовываем содержимое полей:
			// в поле «content» подставляем HTML вместо Markdown,
			// а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
			/**@var \Zend\Stdlib\ParametersInterface $post */
			$post = $request->getPost();
			$post[\Dfe\Markdown\Setup\InstallSchema::F__MARKDOWN] = $post['content'];
			$post['content'] = $post['content' . \Dfe\Markdown\FormElement::HTML_COMPILED];
		}
	}
}


