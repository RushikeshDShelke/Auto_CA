<?php
/**
 * @author Evince Team
 * @copyright Copyright (c) 2018 Evince (http://evincemage.com/)
 */

namespace DotTrack\Trackorder\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \DotTrack\Quickorder\Helper\Data
     */
    protected $_helper;

    /**
     * View constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \DotTrack\Quickorder\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \DotTrack\Trackorder\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }
    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
public function execute()
    {
        $enabled = $this->_helper->isEnabled();
        if (!$enabled) {
            return $this->resultRedirectFactory->create()->setRefererUrl();
        }

        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}

