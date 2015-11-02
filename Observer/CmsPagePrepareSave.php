<?php
namespace Dfe\Markdown\Observer;
use Magento\Framework\Event\ObserverInterface;
class CmsPagePrepareSave implements ObserverInterface {
	/**
	 * 2015-11-02
	 * Цель обработки этого события — перетасовка содержимое полей:
	 * в поле «content» подставляем HTML вместо Markdown (содержимое поля «content_html_compiled»),
	 * а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @see \Magento\Cms\Controller\Adminhtml\Page\Save::executeInternal()
	 * https://github.com/magento/magento2/blob/f578e54e093c31378ca981cfe336f7e651194585/app/code/Magento/Cms/Controller/Adminhtml/Page/Save.php#L57-L60
		$this->_eventManager->dispatch(
			'cms_page_prepare_save',
			['page' => $model, 'request' => $this->getRequest()]
		);
	 * @param \Magento\Framework\Event\Observer $o
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			/** @var \Magento\Cms\Model\Page $page */
			$page = $o['page'];
			/** @var \Magento\Framework\App\RequestInterface $request */
			$request = $o['request'];
			// Обратите внимание, что мы перетасовываем содержимое полей:
			// в поле «content» подставляем HTML вместо Markdown,
			// а в поле «markdown» — прежнее содержимое поля «content» (т.е. Markdown).
			$page->addData([
				\Dfe\Markdown\Setup\InstallSchema::F__MARKDOWN => $page['content']
				,'content' => $request->getParam('content' . \Dfe\Markdown\FormElement::HTML_COMPILED)
			]);
		}
	}
}


