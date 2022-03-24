<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Plugin\Adminhtml\Customer\Save;

use Magento\Customer\Controller\Adminhtml\Index\Save;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\Error as ErrorMessage;
use Magento\Framework\Message\ManagerInterface;

/**
 * Cminds Marketplace admin customer save controller plugin.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Plugin
{
    /**
     * Message manager object.
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Request object.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Object constructor.
     *
     * @param ManagerInterface $messageManager Message manager object.
     * @param RequestInterface $request        Request object.
     */
    public function __construct(
        ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * After execute plugin.
     * Set proper success/error messages depends if supplier flag.
     *
     * @param Save     $subject        Subject object.
     * @param Redirect $resultRedirect Redirect object.
     *
     * @return Redirect
     */
    public function afterExecute(
        Save $subject,
        Redirect $resultRedirect
    ) {
        $isSupplier = $this->request->getParam('supplier', false);
        if ($isSupplier === false) {
            return $resultRedirect;
        }

        $errorOccurred = false;
        $messageCollection = $this->messageManager->getMessages(false);
        $messageItems = $messageCollection->getItems();

        foreach ($messageItems as $message) {
            if ($errorOccurred === false && $message instanceof ErrorMessage) {
                $errorOccurred = true;
            }

            $text = $message->getText();
            $text = str_replace(__('customer'), __('supplier'), $text);

            $message->setText($text);
        }

        $requestData = $this->request->getPostValue();
        $customerId = isset($requestData['customer']['entity_id'])
            ? $requestData['customer']['entity_id']
            : null;

        $returnToEdit = (bool)$subject->getRequest()->getParam('back', false);

        if ($errorOccurred || $returnToEdit) {
            if ($customerId) {
                $resultRedirect->setPath(
                    'customer/*/edit',
                    [
                        'id' => $customerId,
                        'supplier' => true,
                        '_current' => true,
                    ]
                );
            } else {
                $resultRedirect->setPath(
                    'customer/*/new',
                    [
                        'supplier' => true,
                        '_current' => true,
                    ]
                );
            }
        } else {
            $resultRedirect->setPath('supplier/suppliers/index');
        }

        return $resultRedirect;
    }
}
