<?php
/**
 * Class Download
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\FacebookShopIntegration\Controller\Adminhtml\CsvLog;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action\Context;

/**
 * Class Download
 *
 * @category Sparsh
 * @package  Sparsh_FacebookShopIntegration
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Download extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $downloader;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directory;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * Download constructor.
     * @param Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $directory
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->downloader =  $fileFactory;
        $this->directory = $directory;
        $this->file = $file;
        $this->request = $request;
        $this->resultRawFactory      = $resultRawFactory;
        $this->_messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * CSV download action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {

        $fileName = $this->request->getParam('filename');

        $file = $this->directory->getPath("pub")."/import/sparsh/facebook_shop/".$fileName;

        /**
         * do file download
         */
        try {
            $this->downloader->create(
                $fileName,
                $this->file->fileGetContents($file),
                DirectoryList::VAR_DIR
            );
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            return $this->_redirect('*/*/');
        }
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->file->fileGetContents($file)); //set content for download file here
        return $resultRaw;
    }
}
