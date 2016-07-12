<?php
namespace Dfe\Markdown;
/** @method static Settings s() */
class Settings extends \Df\Core\Settings {
	/** @return bool */
	public function enable() {return $this->b('enable');}

	/**
	 * @override
	 * @used-by \Df\Core\Settings::v()
	 * @return string
	 */
	protected function prefix() {return 'df_backend/markdown/';}
}