<?php
namespace Dfe\Markdown\Plugin\Ui\Component\Form\Element;
use Df\Framework\Form\Element\Editor;
use Dfe\Markdown\FormElement;
use Dfe\Markdown\Settings;
use Magento\Ui\Component\Form\Element\Wysiwyg as Sb;
class Wysiwyg extends Sb {
	/**
	 * 2016-01-06
	 * @override
	 * @see \Magento\Ui\Component\Form\Element\Wysiwyg::__construct()
	 */
	function __construct() {}

	/**
	 * 2016-01-06
	 * Цель плагина — ...
	 * @see \Magento\Ui\Component\Form\Element\Wysiwyg::prepare()
	 * https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/AbstractComponent.php#L83-L113
	 * @param Sb $sb
	 */
	function beforePrepare(Sb $sb) {
		if (Settings::s()->enable()) {
			/** @var \Magento\Framework\Data\Form\Element\Editor $editor */
			$editor = $sb->editor; # 2016-02-18 В предыдущих версиях Magento свойство называлось «editorElement».
			$e = df_new_omd(FormElement::class, $editor->getData()); /** @var FormElement $e */
			$e->setForm($editor->getForm());
			/**
			 * 2016-01-06
			 * @see \Magento\Framework\Data\Form\Element\Editor::__construct()
			 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/Editor.php#L35
			 * @see \Magento\Framework\Data\Form\Element\AbstractElement::setType()
			 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/AbstractElement.php#L197-L198
			 */
			$e->setType($editor->getType());
			/**
			 * 2016-01-06
			 * https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/Form/Element/Wysiwyg.php#L49
			 * $this->editorElement->setConfig($wysiwygConfig->getConfig());
			 */
			/**
			 * 2016-01-06
			 * 1) https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/Form/Element/Wysiwyg.php#L50
			 * $data['config']['content'] = $editorElement->getElementHtml();
			 * 2) https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/AbstractComponent.php#L60
			 * $this->_data = array_replace_recursive($this->_data, $data);
			 */
			/** @var array(string => mixed)|null $config */
			$sb['config'] = [
				'component' => 'Dfe_Markdown/component' # 2016-01-08 Вместо «Magento_Ui/js/form/element/wysiwyg»
				,'content' => Editor::wrapIntoContainerSt($editor, $e->componentHtml())
				,'dfeConfig' => $e->config()
			] + df_eta($sb['config']);
		}
	}
}


