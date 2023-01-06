<?php
namespace Dfe\Markdown;
/** @method static Settings s() */
/** @final Unable to use the PHP «final» keyword here because of the M2 code generation. */
final class Settings extends \Df\Config\Settings {
	/**
	 * @override
	 * @see \Df\Config\Settings::prefix()
	 * @used-by \Df\Config\Settings::v()
	 */
	protected function prefix():string {return 'df_backend/markdown';}
}