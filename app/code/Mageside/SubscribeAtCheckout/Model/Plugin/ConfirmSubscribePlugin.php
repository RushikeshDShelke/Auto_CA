<?php
/**
 * Copyright © Mageside. All rights reserved.
 * See MS-LICENSE.txt for license details.
 */
namespace Mageside\SubscribeAtCheckout\Model\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageside\SubscribeAtCheckout\Helper\Config;
use Psr\Log\LoggerInterface;

class ConfirmSubscribePlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SubscriberFactory
     */
    private $subscriberFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $configHelper;

    /**
     * ConfirmSubscribePlugin constructor.
     * @param SubscriberFactory $subscriberFactory
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $configHelper
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Config $configHelper
    ) {
        $this->subscriberFactory = $subscriberFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->configHelper = $configHelper;
    }

    /**
     * @param \Magento\Newsletter\Model\SubscriptionManager $subject
     * @param callable $proceed
     * @param string $email
     * @param int $storeId
     * @return Subscriber
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSubscribe(
        \Magento\Newsletter\Model\SubscriptionManager $subject,
        callable $proceed,
        string $email,
        int $storeId
    ) {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $subscriber = $this->subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
        $currentStatus = (int)$subscriber->getStatus();
        if ($currentStatus === Subscriber::STATUS_SUBSCRIBED) {
            return $subscriber;
        }
        if (!$this->configHelper->getConfigModule('send_request_email')) {
            $status = Subscriber::STATUS_SUBSCRIBED;
        } else {
            $status = $this->isConfirmNeed($storeId) ? Subscriber::STATUS_NOT_ACTIVE : Subscriber::STATUS_SUBSCRIBED;
        }
        if (!$subscriber->getId()) {
            $subscriber->setSubscriberConfirmCode($subscriber->randomSequence());
            $subscriber->setSubscriberEmail($email);
        }
        $subscriber->setStatus($status)
            ->setStoreId($storeId)
            ->save();

        $this->sendEmailAfterChangeStatus($subscriber);

        return $subscriber;
    }

    /**
     * Sends out email to customer after change subscription status
     *
     * @param Subscriber $subscriber
     * @return void
     */
    private function sendEmailAfterChangeStatus(Subscriber $subscriber): void
    {
        $status = (int)$subscriber->getStatus();
        if ($status === Subscriber::STATUS_UNCONFIRMED) {
            return;
        }

        try {
            switch ($status) {
                case Subscriber::STATUS_UNSUBSCRIBED:
                    $subscriber->sendUnsubscriptionEmail();
                    break;
                case Subscriber::STATUS_SUBSCRIBED:
                    $subscriber->sendConfirmationSuccessEmail();
                    break;
                case Subscriber::STATUS_NOT_ACTIVE:
                    $subscriber->sendConfirmationRequestEmail();
                    break;
            }
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
    }

    /**
     * @param int $storeId
     * @return bool
     */
    private function isConfirmNeed(int $storeId): bool
    {
        return (bool)$this->scopeConfig->isSetFlag(
            Subscriber::XML_PATH_CONFIRMATION_FLAG,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
