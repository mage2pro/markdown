<?php
namespace Dfe\Markdown;
use Magento\Framework\Data\Form\AbstractForm;
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
	public function beforeAddField(
		AbstractForm $subject, $elementId, $type, $config, $after = false
	) {
		if ('editor' === $type && \Dfe\Markdown\Settings::s()->enable()) {
			$type = 'Dfe\Markdown\FormElement';
		}
		return [$elementId, $type, $config, $after];
	}
}
