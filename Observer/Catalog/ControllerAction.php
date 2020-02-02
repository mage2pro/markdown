<?php
namespace Dfe\Markdown\Observer\Catalog;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
abstract class ControllerAction implements ObserverInterface {
	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction::processPost()
	 * @param array(string => string|array) $post
	 * @param string $shortKey
	 */
	abstract protected function handleCustomValue(array &$post, $shortKey);

	/**
	 * 2015-11-04
	 * @used-by \Dfe\Markdown\Observer\Catalog\ControllerAction::processPost()
	 * @return string
	 */
	abstract protected function suffix();

	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @param O $o
	 */
	function execute(O $o) {
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
	 * @used-by execute()
	 * @used-by processPost()
	 * @param array(string => string|array) $post
	 * @return array(string => string|array)
	 */
	private function processPost(array $post) {
		$keysToUnset = [];  /** @var string[] $keysToUnset */
		foreach ($post as $k => $v) {/** @var string $k */ /** @var string|array $value */
			if (is_array($v)) {
				$post[$k] = $this->processPost($v);
			}
			else if (dfa($post, $kCustom = $k . $this->suffix())) { /** @var string $kCustom */
				$this->handleCustomValue($post, $k);
				$keysToUnset[]= $kCustom;
			}
		}
		return dfa_unset($post, $keysToUnset);
	}

	const MARKDOWN_SUFFIX = '_markdown';
}


