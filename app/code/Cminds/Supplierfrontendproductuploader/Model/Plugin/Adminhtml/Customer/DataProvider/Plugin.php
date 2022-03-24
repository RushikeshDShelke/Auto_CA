<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Plugin\Adminhtml\Customer\DataProvider;

use Cminds\Supplierfrontendproductuploader\Helper\Data as DataHelper;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Customer\DataProvider;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

/**
 * Cminds Marketplace admin customer edit block plugin.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Plugin
{
    /**
     * Data helper object.
     *
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * Registry object.
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Request object.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * State object.
     *
     * @var State
     */
    protected $appState;

    /**
     * Object constructor.
     *
     * @param DataHelper       $dataHelper Data helper object.
     * @param Registry         $registry   Registry object.
     * @param RequestInterface $request    Request object.
     * @param State            $appState   State object.
     */
    public function __construct(
        DataHelper $dataHelper,
        Registry $registry,
        RequestInterface $request,
        State $appState
    ) {
        $this->dataHelper = $dataHelper;
        $this->registry = $registry;
        $this->request = $request;
        $this->appState = $appState;
    }

    /**
     * Return bool value if supplier is currently handled.
     *
     * @return bool
     */
    protected function isSupplier()
    {
        $isSupplier = $this->request->getParam('supplier', false);
        if ($isSupplier !== false) {
            return true;
        }

        $customerId = $this->request->getParam('id', false);
        if ($customerId === false) {
            return false;
        }

        $isSupplier = $this->dataHelper
            ->isSupplier($customerId);

        return (bool)$isSupplier;
    }

    /**
     * After get config data plugin.
     *
     * @param DataProvider $subject Data provider object.
     * @param array        $data    Data array.
     *
     * @return array
     */
    public function afterGetConfigData(
        DataProvider $subject,
        array $data
    ) {
        $isSupplier = $this->isSupplier();
        if ($isSupplier === false) {
            return $data;
        }

        $areaCode = $this->appState->getAreaCode();
        if ($areaCode !== Area::AREA_ADMINHTML) {
            return $data;
        }
        
        if($this->request->getParam('supplier') == 1){
            $data['submit_url'] .= 'supplier/1/';
        }    
        return $data;
    }
}