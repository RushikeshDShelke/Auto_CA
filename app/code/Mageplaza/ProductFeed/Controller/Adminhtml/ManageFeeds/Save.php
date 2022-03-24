<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Controller\Adminhtml\AbstractManageFeeds;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\FeedFactory;
use RuntimeException;
use Zend_Serializer_Exception;

/**
 * Class Save
 * @package Mageplaza\ProductFeed\Controller\Adminhtml\ManageFeeds
 */
class Save extends AbstractManageFeeds
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Save constructor.
     *
     * @param FeedFactory $feedFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param Data $helperData
     */
    public function __construct(
        FeedFactory $feedFactory,
        Registry $coreRegistry,
        Context $context,
        EncryptorInterface $encryptor,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->encryptor = $encryptor;

        parent::__construct($feedFactory, $coreRegistry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws Zend_Serializer_Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $data = $this->getRequest()->getPost('feed');
        if (isset($data['fields_map']) && $data['fields_map']) {
            $data['fields_map'] = Data::jsonEncode($data['fields_map']);
        }
        if (isset($data['category_map']) && $data['category_map']) {
            $data['category_map'] = $this->helperData->serialize($data['category_map']);
        }
        if (isset($data['cron_run_time']) && $data['cron_run_time']) {
            $data['cron_run_time'] = implode(',', $data['cron_run_time']);
        }
        if (isset($data['password']) && $data['password'] && $data['is_encryptor'] === '1') {
            $data['password'] = $this->encryptor->decrypt($data['password']);
        }

        $conditionData = $this->getRequest()->getPost('rule');
        $feed = $this->initFeed();
        $feed->addData($data);
        $feed->loadPost($conditionData);

        try {
            $feed->save();
            $this->messageManager->addSuccessMessage(__('The feed has been saved.'));
            $this->_getSession()->setData('mageplaza_productfeed_feed_data', false);

            if ($this->getRequest()->getParam('back')) {
                $resultRedirect->setPath('mpproductfeed/*/edit', ['feed_id' => $feed->getId(), '_current' => true]);
            } else {
                $resultRedirect->setPath('mpproductfeed/*/');
            }

            return $resultRedirect;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (RuntimeException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Feed.'));
        }

        $this->_getSession()->setData('mageplaza_productfeed_feed_data', $data);

        $resultRedirect->setPath('mpproductfeed/*/edit', ['feed_id' => $feed->getId(), '_current' => true]);

        return $resultRedirect;
    }
}
