<?php

namespace Dotzot\Grid\Controller\Adminhtml\Secondgrid;

use Magento\Framework\App\Action\Context;

class Downloadcsv extends \Magento\Framework\App\Action\Action
{
     /**
     * @var Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_downloader;
 
    /**
     * @var Magento\Framework\Filesystem\DirectoryList
     */
    protected $_directory;
 
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem\DirectoryList $directory
    ) {
        $this->_downloader =  $fileFactory;
        $this->directory = $directory;
        parent::__construct($context);
    }
 
    public function execute()
    {
        $fileName = 'docket.csv';
        $file = $this->directory->getPath("app")."/downloadsample/".$fileName;
        return $this->_downloader->create(
            $fileName,
            @file_get_contents($file)
        );
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('grid/secondgrid/index');
    }
}