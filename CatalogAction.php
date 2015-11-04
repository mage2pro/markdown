<?php
namespace Dfe\Markdown;
class CatalogAction {
	/**
	 * @used-by \Dfe\Markdown\Observer\Catalog\PrepareForm::execute()
	 * @return int
	 */
	public function entityId() {return df_request('id');}

	/** @return \Dfe\Markdown\CatalogAction */
	public static function s() {static $r; return $r ? $r : $r = new self;}
}

