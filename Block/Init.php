<?php
namespace Dfe\Markdown\Block;
class Init extends \Magento\Backend\Block\AbstractBlock {
	/**
	 * @override
	 * @see \Magento\Backend\Block\AbstractBlock::_construct()
	 * @return void
	 */
	protected function _construct() {
		/** http://devdocs.magento.com/guides/v2.0/architecture/view/page-assets.html#m2devgde-page-assets-api */
		df_page()->addPageAsset('Dfe_Markdown::main.js');
		df_page()->addPageAsset('Dfe_Markdown::main.css');
		df_page()->addPageAsset('Dfe_Markdown::simple-mde/main.css');
	}
}
