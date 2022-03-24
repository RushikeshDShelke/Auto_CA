<?php

/**
 * Save Athlete2 Settings interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Athlete2\Model\DynamicStyle\Generator as DynamicStyleGenerator;

class SaveAthlete2Settings implements ObserverInterface
{

    /**
     * Dynamic Style generator
     *
     * @var DynamicStyleGenerator
     */
    protected $_DynamicStyleGenerator;

    /**
     *
     *
     * @var Helper
     */
    protected $_helper;
    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * Constructor
     *
     * @param DynamicStyleGenerator $generator
     */
    public function __construct(
        ManagerInterface $messageManager,
        DynamicStyleGenerator $generator,
        Helper $helper
    ) {
        $this->_messageManager = $messageManager;
        $this->_DynamicStyleGenerator = $generator;
        $this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $this->_helper->check();
        } catch (Exception $e) {
            $this->_messageManager->addErrorMessage(__($e->getMessage()));
        }
        $this->_DynamicStyleGenerator->generate('settings', $observer->getData('website'), $observer->getData('store'));
        $this->_helper->validate();
    }
}
