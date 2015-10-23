<?php
namespace Dfe\Markdown\Setup;
use \Magento\Framework\DB\Adapter;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class InstallSchema implements InstallSchemaInterface {
	/**
	 * 2015-10-23
	 * @override
	 * @see InstallSchemaInterface::install()
	 * @param SchemaSetupInterface $setup
	 * @param ModuleContextInterface $context
	 * @return void
	 */
	public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$setup->startSetup();
		$this->_conn = $setup->getConnection();
		$this->addMarkdownColumn([
			'catalog_category_entity_text', 'catalog_product_entity_text', 'cms_block', 'cms_page'
		]);
		$setup->endSetup();
	}

	/**
	 * @param string[] $tableNames
	 * @return void
	 */
	private function addMarkdownColumn(array $tableNames) {
		foreach ($tableNames as $tableName) {
			/** @var string $tableName */
			$this->_conn->addColumn(rm_table($tableName), self::F__MARKDOWN, 'text');
		}
	}

	const F__MARKDOWN = 'dfe_markdown';
	/** @var Adapter\Pdo\Mysql|Adapter\AdapterInterface */
	private $_conn;
}