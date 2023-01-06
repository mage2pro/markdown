<?php
namespace Dfe\Markdown\Observer\Cms;
use Laminas\Stdlib\Parameters as Params;
use Laminas\Stdlib\ParametersInterface as IParams;
use Magento\Framework\App\RequestInterface as IRequest;
use Magento\Framework\App\Request\Http;
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
 *	$this->_eventManager->dispatch(
 *		'controller_action_predispatch_' . $request->getFullActionName(),
 *		$eventParameters
 *	);
 */
final class Predispatch implements ObserverInterface {
	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 */
	function execute(O $o):void {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			$req = $o['request']; /** @var IRequest|Http $req */
			# Обратите внимание, что мы перетасовываем содержимое полей:
			# в поле «content» подставляем HTML вместо Markdown,
			# а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
			$post = $req->getPost(); /** @var IParams|Params $post */
			if ($html = $post['content' . \Dfe\Markdown\FormElement::HTML_COMPILED]) { /** @var string $html */
				# 2015-11-03 Перетасовываем данные только при их наличии. Мало ли что...
				$post[\Dfe\Markdown\Setup\UpgradeSchema::F__MARKDOWN] = $post['content'];
				$post['content'] = $html;
			}
		}
	}
}