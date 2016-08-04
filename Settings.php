<?php
namespace Dfe\Markdown;
/** @method static Settings s() */
class Settings extends \Df\Core\Settings {
	/**
	 * @override
	 * @see \Df\Core\Settings::prefix()
	 * @used-by \Df\Core\Settings::v()
	 * @return string
	 */
	protected function prefix() {return 'df_backend/markdown/';}
}