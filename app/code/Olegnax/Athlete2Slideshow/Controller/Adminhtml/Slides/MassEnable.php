<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Athlete2Slideshow
 * @copyright   Copyright (c) 2020 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2Slideshow\Controller\Adminhtml\Slides;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Olegnax\Athlete2Slideshow\Model\ResourceModel\Slides\CollectionFactory;
use Olegnax\Athlete2Slideshow\Model\Slides;

class MassEnable extends Action
{
    const STATUS = 1;

    /**
     * @var string
     */
    const ADMIN_RESOURCE = 'Olegnax_Athlete2Slideshow::Slides_Edit';
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * MassStatus constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $_collection = $this->_collectionFactory->create();
        count($_collection);
        $collection = $this->_filter->getCollection($_collection);
        $sliderUpdated = 0;
        /** @var Slides $slider */
        foreach ($collection as $slider) {
            try {
                $slider->setStatus(static::STATUS)
                    ->save();

                $sliderUpdated++;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(
                    __(
                        'Something went wrong while updating status for %1.',
                        $slider->getName()
                    )
                );
            }
        }

        if ($sliderUpdated) {
            $this->messageManager->addSuccessMessage(
                __(
                    'A total of %1 record(s) have been updated.',
                    $sliderUpdated
                )
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
