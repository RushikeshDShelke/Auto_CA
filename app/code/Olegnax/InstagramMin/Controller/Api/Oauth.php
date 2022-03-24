<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Controller\Api;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Olegnax\InstagramMin\Helper\Helper;
use Olegnax\InstagramMin\Model\Client;
use Psr\Log\LoggerInterface;

class Oauth extends Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Helper
     */
    protected $helper;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var LayoutInterface
     */
    protected $layout;
    /**
     * @var Client
     */
    protected $client;

    /**
     * Constructor
     *
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Helper $helper
     * @param LayoutInterface $layout
     * @param Client $client
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Helper $helper,
        LayoutInterface $layout,
        Client $client,
        PageFactory $resultPageFactory
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->layout = $layout;
        $this->client = $client;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        $message = '';
        $result = [];

        $error = (int)$this->getRequest()->getParam("error", 0);
        $storeId = (int)$this->getRequest()->getParam("store_id", Store::DEFAULT_STORE_ID);
        if (0 < $error) {
            $message = $this->getRequest()->getParam("message");
            $this->CreateError($message, $storeId);
        } else {
            $token = $this->helper->getModuleConfig(Helper::XML_PATH_TOKEN, $storeId);
            $data = $this->getRequest()->getParam("data");

            if (empty($data)) {
                $message = __('Received an empty token!');
                $this->CreateError($message, $storeId);
            } else {
                try {
                    $_data1 = base64_decode($data);
                    $_data2 = str_replace($token, '', $_data1);
                    $_data3 = base64_decode($_data1);
                    $result = json_decode($_data3, true);

                    if (empty($result)) {
                        $message = __('Error converting token!');
                        $this->CreateError($message, $storeId);
                    } elseif (isset($result['time']) && 0 < $result['time']) {
                        $result['expire'] -= $result['time'] - time();
                        unset($result['time']);
                        if (isset($result['user_id'])) {
                            $this->client->setUserId($result['user_id']);
                        }
                        if (isset($result['access_token'])) {
                            $this->client->setToken($result['access_token']);
                        }
                        $userdata = $this->client->getUsername();
                        $result['user_id'] = $userdata['id'];
                        $result['username'] = $userdata['username'];
                        $result['account_type'] = $userdata['account_type'];
                        foreach ($result as $key => $value) {
                            $this->helper->setModuleConfig(
                                "oauth/" . $key,
                                $value,
                                0 < $storeId ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                                $storeId
                            );
                        }
                        $this->helper->clearCache();
                    } else {
                        $message = __('Token save error!');
                        $this->CreateError($message, $storeId);
                    }
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $this->CreateError($message, $storeId);
                }
            }
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_RAW)
            ->setContents($this->getContents($message, $result))
            ->setHeader(
                'Cache-Control',
                'no-store, no-cache, must-revalidate, max-age=0',
                true
            );
    }

    protected function CreateError($message, $storeId)
    {
        $this->logger->debug("Instagram: " . $message);
        foreach (['access_token', 'user_id', 'expire'] as $key) {
            $this->helper->setModuleConfig(
                "oauth/" . $key,
                '',
                0 < $storeId ? ScopeInterface::SCOPE_STORE : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $storeId
            );
        }
    }

    protected function getContents($message, $result)
    {
        ob_start(); ?>
		<script>
            (function (parent, message, data) {
                parent.getElementById("olegnax_instagram_oauth_access_token_message").textContent = message;
                parent.getElementById("olegnax_instagram_oauth_access_token_message").style.display = message.length ? 'block' : 'none';
                parent.getElementById("olegnax_instagram_oauth_access_token").classList[(data['user_id'] || '').length ? 'remove' : 'add']('regenerate');
                parent.getElementById("olegnax_instagram_oauth_user_id").value = data['user_id'] || '';
                parent.getElementById("olegnax_instagram_oauth_username").value = data['username'] || '';
                window.opener.console.log(parent.getElementById("olegnax_instagram_oauth_access_token_message").textContent, message, data);
            })(window.opener.document, "<?= $message ?>", <?= json_encode($result) ?>);
            window.close();
		</script>
        <?php
        return ob_get_clean();
    }
}