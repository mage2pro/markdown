<?php
namespace Dfe\Markdown;
use Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg;
use Magento\Framework\Data\Form\Element\Textarea;
class FormElement extends Wysiwyg {
	/**
	 * 2015-10-24
	 * @override
	 * @see \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::getAfterElementHtml()
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
			$result = Textarea::getAfterElementHtml();
			if ($this->getIsWysiwygEnabled()) {
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
				$result .= df_x_magento_init('Dfe_Markdown/main', [
					// 2015-11-04
					// Нужно нам для идентификации страницы.
					// Идентификация страницы нужна нам
					// для правильного кэширования содержимого редактора в Local Storage.
					'action' => df_action_name()
					,'core' => df_wysiwyg_config()
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
					,'mediaBaseURL' => df_store()->getBaseUrl(
						\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
					)
					// 2015-11-02
					// Суффикс скрытого элемента формы,
					// который будет содержать результат компиляции из Markdown в HTML.
					,'suffixForCompiled' => self::HTML_COMPILED
				]);
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2015-10-27
	 * Можно, конечно, было пихнуть стили и в @see \Dfe\Markdown\FormElement::getAfterElementHtml().
	 * Сейчас мне кажется, что разницы особой нет. Ну пусть уж будет как есть.
	 *
	 * Результат метода надо обязательно кэшировать!
	 * https://github.com/magento/magento2/issues/2189
	 * https://mage2.pro/t/144
	 *
	 * Обратите внимание, что родительский класс этот метод не вызывает,
	 * и мы устранили этот дефект своим плагином:
	 * @used-by \Df\Framework\Data\Form\Element\TextareaPlugin::afterGetElementHtml()
	 *
	 * «Class @see \Magento\Framework\Data\Form\Element\Textarea
	 * breaks specification of the parent class @see \Magento\Framework\Data\Form\Element\AbstractElement
	 * by not calling the method getBeforeElementHtml (getAfterElementHtml is called)»
	 * https://github.com/magento/magento2/issues/2202
	 * https://mage2.pro/t/150
	 *
	 * @override
	 * @return string
	 */
	public function getBeforeElementHtml() {
		if (!isset($this->{__METHOD__})) {
			/** @var string $result */
			/** http://stackoverflow.com/a/8212262 */
			/** @var string $result */
			$result = Textarea::getBeforeElementHtml();
			if ($this->getIsWysiwygEnabled() && !self::$_cssAdded) {
				/** @uses df_link_inline() */
				$result .= df_link_inline(self::$_css);
				self::$_cssAdded = true;
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2015-10-27
	 * Перекрываем родительский метод,
	 * потому что мы используем этот класс
	 * не только для административных страниц товара и товарного раздела,
	 * но и для самодельных административных страниц,
	 * а там $this->getEntityAttribute() возвращает null.
	 * @override
	 * @see \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg::getIsWysiwygEnabled()
	 * @return bool
	 */
	public function getIsWysiwygEnabled() {
		/** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute|null $a */
		$a = $this['entity_attribute'];
		return $this->_wysiwygConfig->isEnabled() && (!$a || $a['is_wysiwyg_enabled']);
	}

	/**
	 * @override
	 * @see \Magento\Framework\Data\Form\AbstractForm::_construct()
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		/**
		 * 2015-10-26
		 * Вообще-то повторный вызов @uses \Magento\Framework\View\Page\Config::addPageAsset()
		 * не приведёт к повторному добавлению на страницу одних и тех же ресурсов:
		 * @see \Magento\Framework\View\Asset\Collection::add():
		 * https://github.com/magento/magento2/blob/02e0378c33054acb0cdb8d731d1e2b2c2069bc1b/lib/internal/Magento/Framework/View/Asset/Collection.php#L29
		 * Однако вызов @uses \Magento\Framework\View\Page\Config::addPageAsset()
		 * расходует, как мне показалось, слишком много ресурсов,
		 * чтобы выполнять его повторно просто так:
		 * @see \Magento\Framework\View\Asset\Repository::createAsset()
		 */
		/**
		 * 2015-10-27
		 * https://github.com/magento/magento2/issues/2194
		 * «Need the possibility to load CSS asynchronously».
		 * На административных страницах товаров и товарных разделов
		 * @uses \Magento\Framework\View\Page\Config::addPageAsset() работает прекрасно,
		 * потому что метод @see \Magento\Framework\View\Layout::build()
		 * и, соответственно, метод @see \Magento\Framework\View\Layout\Builder::build()
		 * ещё не завершили свою работу.
		 * Однако на административных экранах редактирования самодельных страниц
		 * (и, видимо, в большинстве других мест) рисование формы происходит по-другому,
		 * и там на момент прихода в эту точку программы макет уже собран:
		 * метод @see \Magento\Framework\View\Layout\Builder::build() уже завершил свою работу,
		 * и @see \Magento\Framework\View\Page\Config::addPageAsset() вызывать уже бесполезно.
		 * В такой ситуации добавлять стили приходится другим способом.
		 * Я использую для этого тег link. В HTML 5 его нормально вставлять внутри body:
		 * http://stackoverflow.com/a/4957574
		 */
		if (
			!self::$_cssAdded
			&& !df_state()->hasBlocksBeenGenerated()
			&& $this->getIsWysiwygEnabled()
		) {
			/** http://devdocs.magento.com/guides/v2.0/architecture/view/page-assets.html#m2devgde-page-assets-api */
			/**
			 * 2015-10-25
			 * Обратите внимание, что мы добавляем тут только стили CSS.
			 * JavaScript надо загружать именно через «text/x-magento-init».
			 * Сначала я ошибочно загружал его через
			 * df_page()->addPageAsset('Dfe_Markdown::main.js');
			 * однако это приводило к тому, что содержимое редактора не показывалось
			 * до клика по нему:
			 * http://stackoverflow.com/questions/8349571
			 * http://stackoverflow.com/questions/17086538
			 */
			/** @uses \Magento\Framework\View\Page\Config::addPageAsset() */
			array_map(array(df_page(), 'addPageAsset'), self::$_css);
			self::$_cssAdded = true;
		}
	}

	/**
	 * 2015-11-02
	 * Суффикс скрытого элемента формы,
	 * который будет содержать результат компиляции из Markdown в HTML.
	 * @used-by \Dfe\Markdown\FormElement::getAfterElementHtml()
	 * @used-by \Dfe\Markdown\Observer\CmsPagePrepareSave::execute()
	 */
	const HTML_COMPILED = '_html_compiled';

	/**
	 * 2015-10-25
	 * Порядок загрузки лучше сделать именно таким,
	 * чтобы наши правила CSS загружались позже и переопределяли стандартные.
	 * @var string[]
	 */
	private static $_css= [
		'Dfe_Markdown::highlight/github.css'
		,'Dfe_Markdown::simple-mde/main.css'
		,'Dfe_Markdown::main.css'
	];
	/** @var bool */
	private static $_cssAdded = false;
}