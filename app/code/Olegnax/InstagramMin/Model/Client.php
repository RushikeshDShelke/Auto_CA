<?php


namespace Olegnax\InstagramMin\Model;


use Exception;
use Magento\Framework\Url;
use Magento\Store\Model\StoreManagerInterface;
use Olegnax\InstagramMin\Helper\Helper;
use Olegnax\InstagramMin\Model\Token\InstagramAPI;
use Psr\Log\LoggerInterface;

class Client
{
    const FRONTEND_PATH_OAUTH = 'olegnax_instagram/api/oauth';
    const MAX_POST_COUNT = 20;
    /**
     * @var InstagramAPI
     */
    protected $instagramAPI;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var Url
     */
    protected $urlBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        InstagramAPI $instagramAPI,
        LoggerInterface $logger,
        Url $urlBuilder,
        StoreManagerInterface $storeManager,
        Helper $helper
    ) {
        $this->instagramAPI = $instagramAPI;
        $this->logger = $logger;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
    }

    public function getAuthUrl($store_id)
    {
        $url = $this->helper->getModuleConfig('oauth/authorize');
        $urlQuery = (bool)parse_url($url, PHP_URL_QUERY);
        $url .= ($urlQuery ? '&' : '?') . http_build_query($this->getAuthKeys($store_id));
        return $url;
    }

    public function getAuthKeys($store_id)
    {
        return [
            'referer' => $this->getRedirectUri(),
            'store_id' => $store_id,
            'token' => $this->helper->generateToken(true, $store_id),
        ];
    }

    public function getRedirectUri()
    {
        return $this->urlBuilder->getUrl(static::FRONTEND_PATH_OAUTH);
    }

    public function setToken($accesToken)
    {
        $this->instagramAPI->setToken($accesToken);
        return $this;
    }

    public function setUserId($userId)
    {
        $this->instagramAPI->setUserId($userId);
        return $this;
    }

    public function getUsername($userId = null)
    {
        return $this->instagramAPI->getUser($userId, ['id', 'username', 'account_type']);

    }

    public function getUserMedia()
    {
        $data = $this->instagramAPI->getUserMedia(
            null,
            [
                'id',
                'caption',
                'media_type',
                'media_url',
                'permalink',
                'thumbnail_url',
                'timestamp',
                'username',
                'comments_count',
                'like_count',
                'shortcode',
                'children',
            ]);
        if (isset($data['data'])) {
            $data = $data['data'];
            foreach ($data as &$post) {
                if (isset($post['children'])) {
                    $post['children'] = $post['children']['data'];
                    foreach ($post['children'] as &$subpost) {
                        try {
                            $subpost_id = $subpost['id'];
                            unset($subpost['id']);
                            $new_subpost = $this->getMedia($subpost_id);
                            $subpost = $new_subpost['media_url'];
                        } catch (Exception $e) {
                            $this->logger->warning('Instagram sub post: ' . $e->getMessage());
                        }
                    }
                    $post['children'] = array_filter($post['children']);
                }
                if (isset($post['thumbnail_url'])) {
                    $post['media_url'] = $post['thumbnail_url'];
                    unset($post['thumbnail_url']);
                }
                if (isset($post['children']) && !empty($post['children'])) {
                    $post['media_url'] = $post['children'];
                    unset($post['children']);
                }
                if (isset($post['permalink']) && !isset($post['shortcode'])) {
                    $post['shortcode'] = basename(parse_url($post['permalink'], PHP_URL_PATH));
                }
                if (isset($post['timestamp'])) {
                    $post['timestamp'] = date_create_from_format('Y-m-d\TH:i:sP', $post['timestamp'])->format('Y-m-d H:i:s');
                }
            }

        } else {
            throw new Exception(__('Invalid content received'));
        }

        return $data;
    }

    public function getMedia($mediaId, $fields = [])
    {
        $post = $this->instagramAPI->getMedia(
            $mediaId,
            array_unique(array_merge(
                $fields,
                [
                    'media_url',
                    'thumbnail_url',
                ]
            ))
        );
        if (isset($post['thumbnail_url'])) {
            $post['media_url'] = $post['thumbnail_url'];
            unset($post['thumbnail_url']);
        }

        return $post;
    }
}