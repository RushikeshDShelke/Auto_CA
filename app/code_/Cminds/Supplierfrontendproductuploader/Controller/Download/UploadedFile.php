<?php

namespace Cminds\Supplierfrontendproductuploader\Controller\Download;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Downloadable\Model\LinkFactory;
use Cminds\Supplierfrontendproductuploader\Helper\Data;
use Magento\Downloadable\Helper\File as FileHelper;
use Magento\Downloadable\Helper\Download as DownloadHelper;
use Magento\Framework\Controller\ResultInterface;

class UploadedFile extends Action
{
    /**
     * Cminds Data Helper.
     *
     * @var Data
     */
    private $helper;

    /**
     * Result Factory.
     *
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * Link Entity Factory.
     *
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * File Helper.
     *
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * Download Helper.
     *
     * @var DownloadHelper
     */
    private $downloadHelper;

    /**
     * UploadedFile constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param LinkFactory $linkFactory
     * @param FileHelper $fileHelper
     * @param DownloadHelper $downloadHelper
     */
    public function __construct(
        Context $context,
        Data $helper,
        LinkFactory $linkFactory,
        FileHelper $fileHelper,
        DownloadHelper $downloadHelper
    ) {
        parent::__construct($context);

        $this->helper = $helper;
        $this->linkFactory = $linkFactory;
        $this->fileHelper = $fileHelper;
        $this->downloadHelper = $downloadHelper;
        $this->resultFactory = $context->getResultFactory();
    }

    /**
     * Execute Controller logic.
     *
     * @return ResultInterface|void
     */
    public function execute()
    {
        if ($this->getHelper()->canAccess() === false) {
            if ($this->getHelper()->getCustomerSession()->isLoggedIn()) {
                return $this->force404();
            }

            return $this->redirectToLogin();
        }

        $customerId = $this->getHelper()->getLoggedSupplier()->getId();
        if (!$customerId) {
            $this->force404();
        }

        $linkId = $this->getRequest()->getParam('id');
        if (!$linkId) {
            $this->force404();
        }

        $link = $this->linkFactory->create()
            ->load($linkId);

        if (!$link) {
            $this->force404();
        }

        $productId = $link->getProductId();
        $supplierId = $this->getHelper()->getSupplierIdByProductId($productId);

        if ($supplierId !== $customerId) {
            $this->force404();
        }

        $resource = $this->fileHelper->getFilePath(
            $link->getBasePath(),
            $link->getLinkFile()
        );

        $this->_processDownload($resource, 'file');
    }

    /**
     * Download file, which is displayed on product page in "Vendor Panel".
     *
     * @param $path
     * @param $resourceType
     */
    protected function _processDownload($path, $resourceType)
    {
        /* @var $helper DownloadHelper */
        $helper = $this->downloadHelper;

        $helper->setResource($path, $resourceType);
        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        $this->getResponse()->setHttpResponseCode(
            200
        )->setHeader(
            'Pragma',
            'public',
            true
        )->setHeader(
            'Cache-Control',
            'must-revalidate, post-check=0, pre-check=0',
            true
        )->setHeader(
            'Content-type',
            $contentType,
            true
        );

        if ($fileSize = $helper->getFileSize()) {
            $this->getResponse()->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();

        $helper->output();
    }

    /**
     * Get Cminds Data Helper.
     *
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Redirect user to the 404 page.
     */
    protected function force404()
    {
        $this->_forward('defaultNoRoute');
    }

    /**
     * Redirect user to the login page.
     *
     * @return ResultInterface
     */
    protected function redirectToLogin()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->helper->getSupplierLoginPage());

        return $resultRedirect;
    }
}
