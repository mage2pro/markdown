<?php
namespace Dfe\Markdown;
use Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg;
use Magento\Framework\Data\Form\Element\Textarea;
/** @used-by \Dfe\Markdown\Observer\AdminhtmlCatalogProductEditElementTypes::execute() */
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
			/** @var bool $init */
			static $init = false;
			if (!$init && $this->getIsWysiwygEnabled()) {
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
				$result .= df_x_magento_init('Dfe_Markdown/main');
				$init = true;
			}
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
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
		/** @var bool $init */
		static $init = false;
		if (!$init) {
			/** http://devdocs.magento.com/guides/v2.0/architecture/view/page-assets.html#m2devgde-page-assets-api */
			// 2015-10-25
			// Порядок загрузки лучше сделать именно таким,
			// чтобы наши правила CSS загружались позже и переопределяли стандартные.
			df_page()->addPageAsset('Dfe_Markdown::highlight/github.css');
			df_page()->addPageAsset('Dfe_Markdown::simple-mde/main.css');
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
			df_page()->addPageAsset('Dfe_Markdown::main.css');
			$init = true;
		}
	}
}