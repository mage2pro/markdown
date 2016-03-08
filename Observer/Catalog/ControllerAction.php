<?php
namespace Dfe\Markdown\Observer\Catalog;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
abstract class ControllerAction implements ObserverInterface {
	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\ControllerAction\Catalog::processPost()
	 * @param array(string => string|array) $post
	 * @param string $shortKey
	 * @return void
	 */
	abstract protected function handleCustomValue(array &$post, $shortKey);

	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\ControllerAction\Catalog::processPost()
	 * @return string
	 */
	abstract protected function suffix();

	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @param O $o
	 * @return void
	 */
	public function execute(O $o) {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			/** @var \Magento\Framework\App\RequestInterface|\Magento\Framework\App\Request\Http $request */
			$request = $o['request'];
			/**@var \Zend\Stdlib\ParametersInterface|\Zend\Stdlib\Parameters $post */
			$post = $request->getPost();
			$post->fromArray($this->processPost($post->toArray()));
		}
	}

	/**
	 * 2015-11-04
	 * @param array(string => string|array) $post
	 * @return array(string => string|array)
	 */
	private function processPost(array $post) {
		/** @var string[] $keysToUnset */
		$keysToUnset = [];
		foreach ($post as $key => $value) {
			/** @var string $key */
			/** @var string|array $value */
			if (is_array($value)) {
				$post[$key] = $this->processPost($value);
			}
			else {
				/** @var string $keyCustom */
				$keyCustom = $key . $this->suffix();
				/** @var string|null $valueCustom */
				$valueCustom = dfa($post, $keyCustom);
				if ($valueCustom) {
					$this->handleCustomValue($post, $key);
					$keysToUnset[]= $keyCustom;
				}
			}
		}
		return array_diff_key($post, array_flip($keysToUnset));
	}

	const MARKDOWN_SUFFIX = '_markdown';
}


