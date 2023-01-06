<?php
namespace Dfe\Markdown\Observer\Catalog;
use Laminas\Stdlib\Parameters as Params;
use Laminas\Stdlib\ParametersInterface as IParams;
use Magento\Framework\App\RequestInterface as IRequest;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
abstract class ControllerAction implements ObserverInterface {
	/**
	 * 2015-11-04
	 * @used-by self::processPost()
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction\Postdispatch::handleCustomValue()
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction\Predispatch::handleCustomValue()
	 * @param array(string => string|array) $post
	 */
	abstract protected function handleCustomValue(array &$post, string $shortKey):void;

	/**
	 * 2015-11-04
	 * @used-by self::processPost()
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction\Postdispatch::suffix()
	 * @see \Dfe\Markdown\Observer\Catalog\ControllerAction\Predispatch::suffix()
	 */
	abstract protected function suffix():string;

	/**
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 */
	function execute(O $o):void {
		if (\Dfe\Markdown\Settings::s()->enable()) {
			$req = $o['request']; /** @var IRequest|Http $req */
			$post = $req->getPost(); /** @var IParams|Params $post */
			$post->fromArray($this->processPost($post->toArray()));
		}
	}

	/**
	 * 2015-11-04
	 * @used-by self::execute()
	 * @used-by self::processPost()
	 * @param array(string => string|array) $post
	 * @return array(string => string|array)
	 */
	private function processPost(array $post):array {
		$keysToUnset = [];  /** @var string[] $keysToUnset */
		foreach ($post as $k => $v) {/** @var string $k */ /** @var string|array $value */
			if (is_array($v)) {
				$post[$k] = $this->processPost($v);
			}
			elseif (dfa($post, $kCustom = $k . $this->suffix())) { /** @var string $kCustom */
				$this->handleCustomValue($post, $k);
				$keysToUnset[]= $kCustom;
			}
		}
		return dfa_unset($post, $keysToUnset);
	}
}


