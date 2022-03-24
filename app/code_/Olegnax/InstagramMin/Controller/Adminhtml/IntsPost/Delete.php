<?php

namespace Olegnax\InstagramMin\Controller\Adminhtml\IntsPost;

use Exception;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use Olegnax\InstagramMin\Model\ResourceModel\IntsPost\CollectionFactory;

class Delete extends Action
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var Filter
     */
    protected $_filter;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $status = 0;
        foreach ($collection as $item) {
            try {
                $item->delete();
                $status++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__(
                    "Something went wrong while element \"%1\" was deleted.",
                    $item->getData('ints_id')
                ));
            }
        }

        if (0 < $status) {
            $this->messageManager->addSuccessMessage(__("A total of %1 record(s) have been deleted.", $status));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
