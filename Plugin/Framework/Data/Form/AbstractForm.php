<?php
namespace Dfe\Markdown\Plugin\Framework\Data\Form;
use Dfe\Markdown\Settings;
use Magento\Framework\Data\Form\AbstractForm as Sb;
/**
 * 2015-11-03
 * Этот класс не получается объединить с классом @see \Dfe\Markdown\Plugin\Framework\Data\Form
 */
class AbstractForm {
	/**
	 * 2015-10-27 Цель плагина — замещение при необходимости стандартного редактора нашим.
	 * @see \Magento\Framework\Data\Form\AbstractForm::addField()
	 * @param bool|string|null $after [optional]
	 */
	function beforeAddField(Sb $sb, string $elementId, string $type, array $config, $after = false):array {
		if ('editor' === $type && Settings::s()->enable()
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
			 * методом @see \Dfe\Markdown\Observer\Catalog\ElementTypes::execute()
			 */
			&& df_action_is('cms_block_edit', 'cms_page_edit')
		) {
			$type = \Dfe\Markdown\FormElement::class;
		}
		return [$elementId, $type, $config, $after];
	}
}
