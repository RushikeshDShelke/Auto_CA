<?php


namespace Olegnax\MegaMenu\Helper;


use InvalidArgumentException;
use Magento\Framework\App\Cache\State;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class Cache extends AbstractHelper
{
    const CACHE_TAG = 'OXMEGAMENU';
    const CACHE_ID = 'ox_megamenu';
    const CACHE_LIFETIME = 86400;

    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $cache;
    /**
     * @var State
     */
    protected $cacheState;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var int
     */
    private $storeId;

    /**
     * Cache constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Cache $cache
     * @param State $cacheState
     * @param StoreManagerInterface $storeManager
     * @param Json|null $json
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Cache $cache,
        State $cacheState,
        StoreManagerInterface $storeManager,
        Json $json = null
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->storeManager = $storeManager;
        $this->storeId = $storeManager->getStore()->getId();
        $this->json = $json ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context);
    }

    /**
     * @param string $method
     * @param array $vars
     * @return string
     */
    public function getId($method, $vars = array())
    {
        return base64_encode($this->storeId . static::CACHE_ID . $method . implode('#', (array)$vars));
    }

    /**
     * @param string $cacheId
     * @return bool|object
     */
    public function loadObject($cacheId)
    {
        $result = $this->load($cacheId);

        if (!empty($result)) {
            try {
                $result = $this->json->unserialize($result);
            } catch (InvalidArgumentException $e) {
                return false;
            }
            return $result;
        }

        return false;
    }

    /**
     * @param string $cacheId
     * @return bool|string
     */
    public function load($cacheId)
    {
        if ($this->cacheState->isEnabled(static::CACHE_ID)) {
            return $this->cache->load($cacheId);
        }

        return false;
    }

    /**
     * @param string $data
     * @param string $cacheId
     * @param int $cacheLifetime
     * @return bool
     */
    public function save($data, $cacheId, $cacheLifetime = 0)
    {
        if (!is_string($data)) {
            $data = $this->json->serialize($data);
        }
        if ($this->cacheState->isEnabled(static::CACHE_ID)) {
            $this->cache->save(
                $data,
                $cacheId,
                [static::CACHE_TAG],
                empty($cacheLifetime) ? static::CACHE_LIFETIME : $cacheLifetime
            );
            return true;
        }
        return false;
    }
}