<?php

/**
 * Save Athlete2 Settings interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\Athlete2\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Olegnax\Athlete2\Helper\Helper;
use Olegnax\Athlete2\Model\DynamicStyle\Generator;

class SaveAthlete2License implements ObserverInterface
{

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param Generator $generator
     */
    public function __construct(
        ManagerInterface $messageManager,
        Helper $helper
    ) {
        $this->_messageManager = $messageManager;
        $this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->_messageManager->getMessages(true);
        $code = trim($this->_helper->getSystemDefaultValue('athlete2_license/general/code'));

        try {
            $license = $this->_helper->get();

            if ((empty($license) || !isset($license->data->the_key)) && !empty($code)) {
                $this->activate($code);
            } elseif (!empty($license) && isset($license->data->the_key)) {
                if ($license->data->the_key == $code) {
                    $this->validate();
                } elseif (!empty($code)) {
                    $this->reactivate($code);
                } else {
                    $this->deactivate();
                }
            }
        } catch (Exception $e) {
            $this->_messageManager->addErrorMessage(__($e->getMessage()));
        }
    }

    private function activate($code, $showMessages = true)
    {
        $result = $this->_helper->activate($code);
        if (!$result) {
            $this->_messageManager->addErrorMessage(__("Incorrect response from server"));
            return false;
        }
        if ($showMessages) {
            $this->messages($result);
        }
        if (isset($result->error) && $result->error) {
            $this->_helper->setSystemValue(
                'athlete2_license/general/code',
                '',
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
        return !$result->error;
    }

    private function messages($result)
    {
        if (!$result->error) {
            $this->_messageManager->addSuccessMessage(__($result->message));
            if (isset($result->notices)) {
                foreach ($result->notices as $noticeGroup) {
                    $this->_messageManager->addNoticeMessage(__(implode(' ', $noticeGroup)));
                }
            }
        } elseif (isset($result->errors)) {
            foreach ($result->errors as $errorsGroup) {
                $this->_messageManager->addErrorMessage(__(implode(' ', $errorsGroup)));
            }
        }
    }

    private function validate()
    {
        $this->_helper->validate(true);
    }

    private function reactivate($code)
    {
        if ($this->deactivate(false)) {
            return $this->activate($code);
        }
    }

    private function deactivate($showMessages = true)
    {
        $result = $this->_helper->deactivate();
        if (!$result) {
            $this->_messageManager->addErrorMessage(__("Incorrect response from server"));
            return false;
        }
        if ($showMessages) {
            $this->messages($result);
        }
        return !$result->error;
    }
}
