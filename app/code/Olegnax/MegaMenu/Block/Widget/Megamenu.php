<?php

/**/

namespace Olegnax\MegaMenu\Block\Widget;

use Magento\Customer\Model\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;
use \Olegnax\Core\Block\SimpleTemplate as coreTemplate;

class Megamenu extends coreTemplate implements BlockInterface
{

	protected $_topMenu;

	/**
	 * @var \Magento\Framework\App\Http\Context
	 */
	protected $httpContext;

	/**
	 * Json Serializer Instance
	 *
	 * @var Json
	 */
	private $json;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context, \Olegnax\MegaMenu\Block\Html\Megamenu $topMenu,
		\Magento\Framework\App\Http\Context $httpContext, Json $json = null,
		array $data = []
	)
	{
		$this->_topMenu = $topMenu;

		$this->httpContext = $httpContext;
		$this->json = $json ?: $this->_loadObject(Json::class);
		parent::__construct($context, $data);
	}

	public function getValueOption($path, $default = '')
	{
		if ($this->hasData($path)) {
			return $this->getData($path);
		}
		$value = $this->getConfig($path);
		if (is_null($value)) {
			$value = $default;
		}

		return $value;
	}

	public function getConfig($path, $storeCode = null)
	{
		return $this->_scopeConfig->getValue('ox_megamenu_settings/general/' . $path, ScopeInterface::SCOPE_STORE, $storeCode);
	}

	public function getCacheKeyInfo($newval = [])
	{
		return array_merge([
			'OLEGNAX_MEGAMENU_WIDGET',
			$this->_storeManager->getStore()->getId(),
			$this->_design->getDesignTheme()->getId(),
			$this->httpContext->getValue(Context::CONTEXT_GROUP),
			$this->json->serialize($this->getRequest()->getParams()),
			$this->json->serialize($this->getData()),
			$this->getUrl('*/*/*', ['_current' => true, '_query' => '']),
		], parent::getCacheKeyInfo(), $newval);
	}

	public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
	{
		$this->_topMenu->setData('enable_megamenu', true);
		$data = array_diff_key($this->getData(), ['type' => '', 'module_name' => '', 'cache_lifetime' => '', 'type_name' => '']);
		foreach ($data as $key => $value) {
			$this->_topMenu->setData($key, $value);
		}

		return $this->_topMenu->getHtml($outermostClass, $childrenWrapClass, $limit);
	}

	protected function _construct()
	{
		$this->addData([
			'cache_lifetime' => 86400,
		]);
		if (!$this->hasData('template') && !$this->getTemplate()) {
			$this->setTemplate('Olegnax_MegaMenu::widgetmenu.phtml');
		}
		parent::_construct();
	}

	protected function isEnabled()
	{
		return true;
	}

}
