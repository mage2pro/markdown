<?php
namespace Dfe\Markdown\Observer;
use Magento\Framework\Event\ObserverInterface;
/**
 * 2015-11-02
 * Обработчик события «adminhtml_cms_page_edit_tab_content_prepare_form».
 * Цель обработки этого события —
 * подставовка в поле «content» Markdown вместо HTML.
 * @see \Magento\Cms\Block/Adminhtml\Page\Edit\Tab\Content::_prepareForm()
 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Cms/Block/Adminhtml/Page/Edit/Tab/Content.php#L99
	$this->_eventManager->dispatch(
		'adminhtml_cms_page_edit_tab_content_prepare_form', ['form' => $form]
	);
 */
class AdminhtmlCmsPageEditTabContentPrepareForm implements ObserverInterface {
	/**
	 * 2015-11-02
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @param \Magento\Framework\Event\Observer $o
	 * @return void
	 */
	public function execute(\Magento\Framework\Event\Observer $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			/** @var \Magento\Cms\Model\Page $page */
			$page = df_registry('cms_page');
			/** @var string $markdown */
			$markdown = $page[\Dfe\Markdown\Setup\InstallSchema::F__MARKDOWN];
			// Важное условие!
			// Замещаем HTML на Markdown только при наличии Markdown,
			// иначе ведь администратор мог редактировать ранее статью в обычном редакторе,
			// и у статью будет HTML, но не будет Markdown,
			// и тогда замена HTML на Markdown приведёт к утрате HTML.
			if ($markdown) {
				$page['content'] = $markdown;
			}
		}
	}
}


