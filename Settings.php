<?php
namespace Dfe\Markdown;
class Settings extends \Df\Core\Settings {
	/** @return bool */
	public function enable() {return $this->b('enable');}

	/**
	 * @override
	 * @used-by \Df\Core\Settings::v()
	 * @return string
	 */
	protected function prefix() {return 'dfe_backend/markdown/';}

	/** @return \Dfe\Markdown\Settings */
	public static function s() {static $r; return $r ? $r : $r = df_o(__CLASS__);}
}