<?php
namespace Dfe\Markdown\Setup;
class UpgradeSchema extends \Df\Framework\Upgrade\Schema {
	/**
	 * 2015-10-23
	 * @override
	 * @see \Df\Framework\Upgrade::_process()
	 * @used-by \Df\Framework\Upgrade::process()
	 * @return void
	 */
	protected function _process() {
		if ($this->isInitial()) {
			$this->createTableEav(self::TABLE_CATEGORY, 'catalog_category_entity_text');
			$this->createTableEav(self::TABLE_PRODUCT, 'catalog_product_entity_text');
			/**
			 * 2016-11-04
			 * У нас теперь также есть функция @see df_db_column_add()
			 */
			$this->c()->addColumn(df_table('cms_block'), self::F__MARKDOWN, 'text');
			$this->c()->addColumn(df_table('cms_page'), self::F__MARKDOWN, 'text');
		}
	}

	/**
	 * 2015-10-23
	 * Сначала я ошибочно добавил колонку «dfe_markdown» в таблицу «catalog_product_entity_text»,
	 * однако это привело к тому, что свойства EAV товаров перестали загружаться из базы данных.
	 * Такое происходит из-за внутреннего сбоя
	 * «The used SELECT statements have a different number of columns»,
	 * который Magento нигде не логируется и был обнаружен мной лишь посредством отладки.
	 *
	 * Причиной этого сбоя является объединение Magento через SQL UNION
	 * данных всех таблиц типа catalog_product_entity_* в методе
	 * @see \Magento\Eav\Model\Entity\AbstractEntity::_prepareLoadSelect()
	 * https://github.com/magento/magento2/blob/0be91a56d050791ac0b67a47185d79df31e79329/app/code/Magento/Eav/Model/Entity/AbstractEntity.php#L1049-L1052
	 * Так как таблица «catalog_product_entity_text» после добавления колонки «dfe_markdown»
	 * стала содержать на одну колонку больше, чем другие таблицы catalog_product_entity_*,
	 * то SQL UNION стало приводить к сбою.
	 * Запрос SQL при этом выглядит так:
			SELECT `attr_table`.* FROM `catalog_product_entity_varchar` AS `attr_table`
			INNER JOIN `eav_entity_attribute` AS `set_table`
			ON
					attr_table.attribute_id = set_table.attribute_id
				AND
					set_table.attribute_set_id = '4'
			WHERE (attr_table.entity_id = '4') AND (attr_table.store_id IN (0))
		UNION ALL <тот же самый SELECT для таблицы catalog_product_entity_text>
		UNION ALL <тот же самый SELECT для таблицы catalog_product_entity_decimal>
		UNION ALL <тот же самый SELECT для таблицы catalog_product_entity_datetime>
		UNION ALL <тот же самый SELECT для таблицы catalog_product_entity_int>
	 *
	 * Поэтому поступаем теперь иначе: для сущностей EAV храним наши данные в отдельной таблице.
	 * Так же делает ядро, например, для catalog_product_entity_media_gallery:
			CREATE TABLE IF NOT EXISTS `catalog_product_entity_media_gallery` (
				`value_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Value ID',
				`attribute_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Attribute ID',
				`value` varchar(255) DEFAULT NULL COMMENT 'Value',
				`media_type` varchar(32) NOT NULL DEFAULT 'image' COMMENT 'Media entry type',
				`disabled` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Visibility status',
				PRIMARY KEY (`value_id`),
				KEY `CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_ATTRIBUTE_ID` (`attribute_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
			ALTER TABLE `catalog_product_entity_media_gallery`
			ADD CONSTRAINT `CAT_PRD_ENTT_MDA_GLR_ATTR_ID_EAV_ATTR_ATTR_ID`
				FOREIGN KEY (`attribute_id`)
				REFERENCES `eav_attribute` (`attribute_id`)
				ON DELETE CASCADE
			;
	 * https://github.com/magento/magento2/blob/4197d0ec6053b3f963165021f9e5b3a6a476b3bb/app/code/Magento/Catalog/Setup/InstallSchema.php#L1965-L2033
	 * @used-by install()
	 * @param string $name
	 * @param string $master
	 * @return void
	 */
	private function createTableEav($name, $master) {
		$f_MARKDOWN = self::F__MARKDOWN;
		$f_VALUE_ID = self::F__ID;
		$this->c()->rawQuery("
		CREATE TABLE IF NOT EXISTS `{$this->t($name)}` (
			`value_id` int(11) NOT NULL
			,`{$f_MARKDOWN}` text
			,PRIMARY KEY (`{$f_VALUE_ID}`)
			,FOREIGN KEY (`{$f_VALUE_ID}`)
				REFERENCES `{$this->t($master)}` (`value_id`)
				ON DELETE CASCADE
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
	}

	const TABLE_CATEGORY = 'dfe_markdown_category';
	const TABLE_PRODUCT = 'dfe_markdown_product';
	const F__MARKDOWN = 'markdown';
	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Setup\UpgradeSchema::createTableEav()
	 * @used-by \Dfe\Markdown\CatalogAction::markdownSave()
	 */
	const F__ID = 'value_id';
}