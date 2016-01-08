<?php
namespace Dfe\Markdown;
use Magento\Framework\Data\Form\Element\Textarea;
/**
 * 2016-01-06
 * Наш класс замещает класс @see \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg
 * но отныне не наследуется от него.
 */
/**
 * @method string|null getClass()
 * @method string|null getTitle()
 */
class FormElement extends Textarea {
	/**
	 * 2016-01-06
	 * Нам нужно сделать примерно то же, что делает метод
	 * @see \Magento\Framework\Data\Form\Element\Textarea::getElementHtml()
	 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/Textarea.php#L81-L90
	 * но без вызова @see \Dfe\Markdown\FormElement::getAfterElementHtml()
	 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/Textarea.php#L88
	 * В то же время мы учитываем, как работает
	 * @see \Magento\Framework\Data\Form\Element\Editor::getElementHtml()
	 * https://github.com/magento/magento2/blob/c58d2d/lib/internal/Magento/Framework/Data/Form/Element/Editor.php#L103-L121
	 * @return string
	 */
	public function componentHtml() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_tag('textarea', [
				 'class' => ['textarea', $this->getClass()]
				 ,'title' => $this->getTitle()
			] + df_fe_attrs($this), $this->getEscapedValue());
			if ($this->enabled()) {
				$this->{__METHOD__} .= $this->css();
			}
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-01-06
	 * @used-by \Dfe\Markdown\Plugin\Ui\Component\Form\Element\Wysiwyg::beforePrepare()
	 * @used-by \Dfe\Markdown\FormElement::getAfterElementHtml()
	 * @return array(mixed => mixed)
	 */
	public function config() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = [
				// 2015-11-04
				// Нужно нам для идентификации страницы.
				// Идентификация страницы нужна нам
				// для правильного кэширования содержимого редактора в Local Storage.
				'action' => df_action_name()
				,'core' => df_wysiwyg_config()->getConfig()->getData()
				/**
				 * 2015-10-26
				 * На странице товарного раздела
				 * textarea имеет идентификатор «group4_description»,
				 * где «description» — это $this['html_id'], а «group4_» — это префикс.
				 * Для инициализации редактора нам нужен полный идентификатор,
				 * а для стилизации — наоборот, краткий
				 * (который, кстати, совпадает с кратким именем: значением атрибута «name»).
				 */
				,'cssClass' => $this['name']
				,'id' => $this->getHtmlId()
				/**
				 * 2015-10-30
				 * По аналогии с
				 * @see \Magento\Cms\Helper\Wysiwyg\Images::getImageHtmlDeclaration()
				 * https://github.com/magento/magento2/blob/550f10ef2bb6dcc3ba1ea492b7311d7a80d01560/app/code/Magento/Cms/Helper/Wysiwyg/Images.php#L170
				 * https://mage2.pro/t/153
				 */
				,'mediaBaseURL' => df_media_url()
				// 2015-11-02
				// Суффикс скрытого элемента формы,
				// который будет содержать результат компиляции из Markdown в HTML.
				,'suffixForCompiled' => self::HTML_COMPILED
			];
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-01-06
	 * @used-by \Dfe\Markdown\Plugin\Ui\Component\Form\Element\Wysiwyg::beforePrepare()
	 * @used-by \Dfe\Markdown\FormElement::getAfterElementHtml()
	 * @return bool
	 */
	public function enabled() {
		if (!isset($this->{__METHOD__})) {
			/** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|null $a */
			$a = $this['entity_attribute'];
			$this->{__METHOD__} = df_wysiwyg_config()->isEnabled() && (!$a || $a['is_wysiwyg_enabled']);
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2015-10-24
	 * @override
	 * @see \Magento\Framework\Data\Form\Element\Textarea::getAfterElementHtml()
	 * @used-by \Magento\Framework\Data\Form\Element\Textarea::getElementHtml()
	 * 2015-10-26
	 * Результат метода надо обязательно кэшировать!
	 * https://github.com/magento/magento2/issues/2189
	 * https://mage2.pro/t/144
	 * «Performance bug: getAfterElementHtml() method implementation
	 * in \Magento\Framework\Data\Form\Element\AbstractElement descendants
	 * is sometimes computation expensive
	 * but called by the core multiple times for the same for element without caching»
	 */
	public function getAfterElementHtml() {
		if (!isset($this->{__METHOD__})) {
			/** http://stackoverflow.com/a/8212262 */
			/** @var string $result */
			$result = parent::getAfterElementHtml();
			if ($this->enabled()) {
				$result .= $this->css();
				/**
				 * 2015-10-25
				 * Наш скрипт надо загружать именно через «text/x-magento-init».
				 * Сначала я ошибочно загружал его через
				 * df_page()->addPageAsset('Dfe_Markdown::main.js');
				 * однако это приводило к тому, что содержимое редактора не показывалось
				 * до клика по нему:
				 * http://stackoverflow.com/questions/8349571
				 * http://stackoverflow.com/questions/17086538
				 */
				$result .= df_x_magento_init($this->js(), $this->config());
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-01-06
	 * @used-by \Dfe\Markdown\Plugin\Ui\Component\Form\Element\Wysiwyg::beforePrepare()
	 * @used-by \Dfe\Markdown\FormElement::getAfterElementHtml()
	 * @return string
	 */
	public function js() {return 'Dfe_Markdown/main';}

	/**
	 * 2016-01-08
	 * Порядок загрузки лучше сделать именно таким,
	 * чтобы наши правила CSS загружались позже и переопределяли стандартные.
	 * @return string
	 */
	private function css() {
		return df_link_inline([
			'Df_Core::lib/HighlightJs/github.css'
			,'Dfe_Markdown::lib/SimpleMDE/main.css'
			,'Dfe_Markdown::main.css'
		]);
	}

	/**
	 * 2015-11-02
	 * Суффикс скрытого элемента формы,
	 * который будет содержать результат компиляции из Markdown в HTML.
	 * @used-by \Dfe\Markdown\FormElement::getAfterElementHtml()
	 * @used-by \Dfe\Markdown\Observer\CmsPagePrepareSave::execute()
	 */
	const HTML_COMPILED = '_html_compiled';
}