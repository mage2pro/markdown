<?php
namespace Dfe\Markdown\CatalogAction;
use Dfe\Markdown\CatalogAction;
use Dfe\Markdown\Setup\InstallSchema;
class DbRecord extends \Df\Core\O {
	/** @return string */
	private function attributeCode() {return $this[self::$P__ATTRIBUTE_CODE];}

	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\Catalog\PrepareForm::execute()
	 * @return string|null
	 */
	private function _load() {return $this->fetch(InstallSchema::F__MARKDOWN);}

	/**
	 * 2015-11-04
	 * @param string $value
	 * @return void
	 */
	private function _save($value) {
		/** @var int $id */
		$id = $this->fetch(InstallSchema::F__ID);
		if ($id) {
			df_conn()->update($this->markdownTable()
				, [InstallSchema::F__MARKDOWN => $value]
				, ['? = ' . InstallSchema::F__ID => $id]
			);
		}
		else {
			df_conn()->insert($this->markdownTable(), [
				InstallSchema::F__ID => $this->valueId()
				, InstallSchema::F__MARKDOWN => $value
			]);
		}
	}

	/**
	 * @param string $column
	 * @return string|null
	 */
	private function fetch($column) {
		return
			!$this->valueId()
			? null
			: df_fetch_one($this->markdownTable(), $column, [InstallSchema::F__ID => $this->valueId()])
		;
	}

	/** @return int */
	private function attributeId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_fetch_one_int('eav_attribute', 'attribute_id', [
				'attribute_code' => $this->attributeCode()
				, 'entity_type_id' => $this->entityTypeId()
			]);
		}
		return $this->{__METHOD__};
	}

	/** @return string */
	public function entityType() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = explode('_', df_action_name())[1];
		}
		return $this->{__METHOD__};
	}

	/** @return int */
	public function entityTypeId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} =
				df_eav_config()->getEntityType('catalog_' . $this->entityType())->getId()
			;
		}
		return $this->{__METHOD__};
	}

	/**
	 * @use InstallSchema::TABLE_CATEGORY
	 * @use InstallSchema::TABLE_PRODUCT
	 * @return string
	 */
	private function markdownTable() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_table("dfe_markdown_{$this->entityType()}");
		}
		return $this->{__METHOD__};
	}

	/** @return int */
	private function valueId() {
		if (!isset($this->{__METHOD__})) {
			$this->{__METHOD__} = df_fetch_one_int(
				"catalog_{$this->entityType()}_entity_text"
				, 'value_id'
				, [
					'attribute_id' => $this->attributeId()
					, 'entity_id' => CatalogAction::s()->entityId()
					, 'store_id' => df_store()->getId()
				]
			);
		}
		return $this->{__METHOD__};
	}

	/**
	 * @override
	 * @return void
	 */
	protected function _construct() {
		parent::_construct();
		$this->_prop(self::$P__ATTRIBUTE_CODE, RM_V_STRING_NE);
	}

	/**
	 * @used-by \Dfe\Markdown\Observer\Catalog\PrepareForm::execute()
	 * @param string $attributeCode
	 * @return string|null
	 */
	public static function load($attributeCode) {return self::i($attributeCode)->_load();}

	/**
	 * @used-by \Dfe\Markdown\Observer\ControllerAction\Catalog\Postdispatch::handleCustomValue()
	 * @param string $attributeCode
	 * @param string $value
	 * @return void
	 */
	public static function save($attributeCode, $value) {self::i($attributeCode)->_save($value);}

	/**
	 * @param string $attributeCode
	 * @return \Dfe\Markdown\CatalogAction\DbRecord
	 */
	private static function i($attributeCode) {return new self([
		self::$P__ATTRIBUTE_CODE => $attributeCode
	]);}

	/** @var string */
	private static $P__ATTRIBUTE_CODE = 'attribute_code';
}