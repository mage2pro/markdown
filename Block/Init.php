<?php
namespace Dfe\Markdown\Block;
class Init extends \Magento\Framework\View\Element\Template {
	/**
	 * @override
	 * @see \Magento\Backend\Block\AbstractBlock::_construct()
	 * @return void
	 */
	protected function _construct() {
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
	}
}
