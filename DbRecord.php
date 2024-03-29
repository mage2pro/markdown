<?php
namespace Dfe\Markdown;
use Dfe\Markdown\CatalogAction as A;
use Dfe\Markdown\Setup\UpgradeSchema as U;
# 2015-11-04
final class DbRecord {
	/**
	 * 2017-07-31
	 * @used-by self::load()
	 * @used-by self::save()
	 */
	private function __construct(string $attributeC) {
		$entityType = A::entityType(); /** @var string $entityType */
		$this->_table = df_table("dfe_markdown_{$entityType}");
		$this->_id = df_fetch_one_int(
			"catalog_{$entityType}_entity_text", 'value_id', [
				'attribute_id' => df_fetch_one_int('eav_attribute', 'attribute_id', [
					'attribute_code' => df_param_sne($attributeC, 0)
					,'entity_type_id' => df_eav_config()->getEntityType("catalog_{$entityType}")->getId()
				])
				,'entity_id' => A::entityId() # 2017-08-01 The product or category ID
				,'store_id' => df_store()->getId()
			]
		);
		$this->_record = !$this->_id ? [] : df_fetch_one($this->_table, '*', [U::F__ID => $this->_id]);
	}

	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\Catalog\PrepareForm::execute()
	 * @return string|null
	 */
	static function load(string $attributeC) {return dfa((new self($attributeC))->_record, U::F__MARKDOWN);}

	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction\Postdispatch::handleCustomValue()
	 */
	static function save(string $attributeC, string $value):void {
		$i = new self($attributeC); /** @var self $i */
		($id = dfa($i->_record, U::F__ID) /** @var int|null $id */)
		? df_conn()->update($i->_table, [U::F__MARKDOWN => $value], ['? = ' . U::F__ID => $id])
		: df_conn()->insert($i->_table, [U::F__ID => $i->_id, U::F__MARKDOWN => $value]);
	}

	/**
	 * 2017-08-01
	 * @used-by self::__construct()
	 * @used-by self::save()
	 * @var int|null
	 */
	private $_id;

	/**
	 * 2017-08-01
	 * @used-by self::__construct()
	 * @used-by self::load()
	 * @used-by self::save()
	 * @var array(string => string|int)
	 */
	private $_record;

	/**
	 * 2017-08-01
	 * @used-by self::__construct()
	 * @used-by self::save()
	 * @var string
	 */
	private $_table;
}