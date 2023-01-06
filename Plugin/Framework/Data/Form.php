<?php
namespace Dfe\Markdown\Plugin\Framework\Data;
use Dfe\Markdown\Settings;
use Magento\Framework\Data\Form as Sb;
/**
 * 2015-11-03
 * Этот класс не получается объединить с классом @see \Dfe\Markdown\Plugin\Framework\Data\Form\AbstractForm
 */
class Form {
	/**
	 * 2015-11-03
	 * Цель плагина — подмена содержимого поля «content» с HTML на Markdown
	 * перед редактированием содержимого.
	 *
	 * Сначала я делал это более точечным и желательным способом:
	 * через обработку события «adminhtml_cms_page_edit_tab_content_prepare_form»:
	 * @see \Magento\Cms\Block/Adminhtml\Page\Edit\Tab\Content::_prepareForm()
	 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Cms/Block/Adminhtml/Page/Edit/Tab/Content.php#L99
		$this->_eventManager->dispatch(
			'adminhtml_cms_page_edit_tab_content_prepare_form', ['form' => $form]
		);
	 *
	 * Однако оказалось, что для самодельных блоков аналогичное событие отсутствует:
	 * https://github.com/magento/magento2/blob/c743dec47b2e5de036eb5638fec44a54bfb31040/app/code/Magento/Cms/Block/Adminhtml/Block/Edit/Form.php#L61-L158
	 * https://github.com/magento/magento2/issues/2248
	 * «Inconsistency: the «adminhtml_cms_page_edit_tab_content_prepare_form» event
	 * is fired on a backend CMS page form creation,
	 * but no any event is fired on a backend CMS block form creation».
	 *
	 * Поэтому приходится делать через плагин.
	 *
	 * @see \Magento\Framework\Data\Form::setValues()
	 * @param array(string => mixed) $v
	 * @return array(array(string => mixed))
	 */
	function beforeSetValues(Sb $sb, array $v):array {
		if (Settings::s()->enable()
			# 2015-11-03
			# В настоящее время это условие необязательно,
			# но на будущее оно полезно: мало ли кто и для каких целей заведёт поле «markdown».
			&& df_action_is('cms_block_edit', 'cms_page_edit')
			/**
			 * 2016-02-26
			 * @see \Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info::_prepareForm() method
			 * does not check whether the $integrationData is null
			 * and passes it to methods which expect an array
			 * https://mage2.pro/t/825
			 * По этой причине убрал «array» из сигнатуры метода.
			 * Иначе получим сбой:
			 * «Recoverable Error: Argument 2 passed to
			 * Dfe\Markdown\Plugin\Framework\Data\Form::beforeSetValues()
			 * must be of the type array, null given»
			 */
			&& $v
			# Важное условие!
			# Замещаем HTML на Markdown только при наличии Markdown,
			# иначе ведь администратор мог редактировать ранее статью в обычном редакторе,
			# и у статью будет HTML, но не будет Markdown,
			# и тогда замена HTML на Markdown приведёт к утрате HTML.
			&& ($markdown = dfa($v, \Dfe\Markdown\Setup\UpgradeSchema::F__MARKDOWN)) /** @var string $markdown */
		) {
			$v['content'] = $markdown;
		}
		return [$v];
	}
}
