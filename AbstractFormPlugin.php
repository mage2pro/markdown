<?php
namespace Dfe\Markdown;
use Magento\Framework\Data\Form\AbstractForm;
/**
 * 2015-11-03
 * Этот класс не получается объединить с классом @see \Dfe\Markdown\FormPlugin
 */
class AbstractFormPlugin {
	/**
	 * 2015-10-27
	 * Цель плагина — замещение при необходимости стандартного редактора нашим.
	 * @see \Magento\Framework\Data\Form\AbstractForm::addField()
	 * @param AbstractForm $subject
	 * @param string $elementId
	 * @param string $type
	 * @param mixed[] $config
	 * @param bool|string|null $after [optional]
	 * @return mixed[]
	 */
	public function beforeAddField(AbstractForm $subject, $elementId, $type, $config, $after = false) {
		if (
			'editor' === $type
			&& \Dfe\Markdown\Settings::s()->enable()
			/**
			 * 2015-11-03
			 * Включаем наш редактор только для избранных типов страниц,
			 * потому что для неучтённых заранее типов страниц
			 * всё равно не будет правильно работать перетасовка полей Markdown <=> HTML.
			 *
			 * Операции с суффиксом «_new» здесь перечислять не надо,
			 * потому что система сама делает для них невидимое (не отражающееся на веб-адресе)
			 * перенаправление на аналогичную операцию с суффиксом «_edit».
			 *
			 * Адреса для каталога («catalog_category_edit» и «catalog_product_edit»)
			 * здесь так же перечислять не надо, потому что для них наш редактор добавляется иначе:
			 * методом @see \Dfe\Markdown\Observer\ElementTypes::execute()
			 */
			&& in_array(df_action_name(), ['cms_block_edit', 'cms_page_edit'])
		) {
			$type = 'Dfe\Markdown\FormElement';
		}
		return [$elementId, $type, $config, $after];
	}
}
