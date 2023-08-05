<?php
namespace Dfe\Markdown\Plugin\Ui\Component\Form\Element;
use Df\Framework\Form\Element\Editor;
use Dfe\Markdown\FormElement;
use Dfe\Markdown\Settings;
use Magento\Framework\Data\Form\Element\Editor as EditorM;
use Magento\Ui\Component\Form\Element\Wysiwyg as Sb;
# 2016-01-06
/** @final Unable to use the PHP «final» keyword here because of the M2 code generation. */
class Wysiwyg extends Sb {
	/**
	 * 2016-01-06
	 * @override
	 * @see \Magento\Ui\Component\Form\Element\Wysiwyg::__construct()
	 */
	function __construct() {}

	/**
	 * 2016-01-06 Цель плагина — ...
	 * @see \Magento\Ui\Component\Form\Element\Wysiwyg::prepare()
	 * https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/AbstractComponent.php#L83-L113
	 * @used-by \Magento\Ui\Component\AbstractComponent::prepareChildComponent():
	 * 		protected function prepareChildComponent(UiComponentInterface $component) {
	 * 			$childComponents = $component->getChildComponents();
	 * 			if (!empty($childComponents)) {
	 * 				foreach ($childComponents as $child) {
	 * 					$this->prepareChildComponent($child);
	 * 				}
	 * 			}
	 *			$component->prepare();
	 * 			return $this;
	 * 		}
	 * https://github.com/magento/magento2/blob/2.4.7-beta1/app/code/Magento/Ui/Component/AbstractComponent.php#L121-L139
	 */
	function beforePrepare(Sb $sb) {
		if (Settings::s()->enable()) {
			# 2016-02-18 В предыдущих версиях Magento свойство называлось «editorElement».
			$ed = $sb->editor; /** @var EditorM $ed */
			$e = df_new_omd(FormElement::class, $ed->getData()); /** @var FormElement $e */
			$e->setForm($ed->getForm());
			/**
			 * 2016-01-06
			 * @see \Magento\Framework\Data\Form\Element\Editor::__construct()
			 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/Editor.php#L35
			 * @see \Magento\Framework\Data\Form\Element\AbstractElement::setType()
			 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/AbstractElement.php#L197-L198
			 */
			$e->setType($ed->getType());
			/**
			 * 2016-01-06
			 * 1) https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/Form/Element/Wysiwyg.php#L49
			 * $this->editorElement->setConfig($wysiwygConfig->getConfig());
			 * 2) https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/Form/Element/Wysiwyg.php#L50
			 * $data['config']['content'] = $editorElement->getElementHtml();
			 * 3) https://github.com/magento/magento2/blob/c58d2d/app/code/Magento/Ui/Component/AbstractComponent.php#L60
			 * $this->_data = array_replace_recursive($this->_data, $data);
			 * 2023-08-05
			 * `$sb['config']`:
			 * 	{
			 * 		"add_directives": true,
			 * 		"add_variables": false,
			 * 		"add_widgets": false,
			 * 		"code": "short_description",
			 * 		"component": "Magento_Ui/js/form/element/wysiwyg",
			 * 		"componentType": "field",
			 * 		"container_class": "hor-scroll",
			 * 		"content":
			 * 			"<div id=\"editorproduct_form_short_description\"
 			 * 			class=\"admin__control-wysiwig hor-scroll\">…</div>",
			 * 		"dataScope": "short_description",
			 * 		"dataType": "textarea",
			 * 		"default": null,
			 * 		"elementTmpl": "ui/content/content",
			 * 		"formElement": "wysiwyg",
			 * 		"globalScope": false,
			 * 		"label": "Short Description",
			 * 		"notice": null,
			 * 		"required": "0",
			 * 		"scopeLabel": "[STORE VIEW]",
			 * 		"sortOrder": 0,
			 * 		"source": "content",
			 * 		"template": "ui/content/content",
			 * 		"use_container": true
			 * 		"visible": "1",
			 * 		"wysiwyg": true,
			 * 		"wysiwygConfigData": {},
			 * 		"wysiwygId": "product_form_short_description"
		 	 * 	}
			 */
			$sb['config'] = [
				'component' => 'Dfe_Markdown/component' # 2016-01-08 Вместо «Magento_Ui/js/form/element/wysiwyg»
				,'content' => Editor::wrapIntoContainerSt($ed, $e->componentHtml())
				,'dfeConfig' => $e->config()
			] + df_eta($sb['config']);
		}
	}
}