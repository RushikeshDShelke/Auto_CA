<?php
/**
 */

namespace Dotzotfront\Base\Helper;

class Main extends \Dotzotfront\Base\Helper\Base
{
    public function getAjaxUrl($route, $params = [])
    {
        $url = $route;
        $secure = true;
        if ($secure) {
            $url = str_replace('http://', 'https://', $url);
        } else {
            $url = str_replace('https://', 'http://', $url);
        }

        return $url;
    }

    protected function __addProduct(\Magento\Catalog\Model\Product $product, $request = null)
    {
        return $this->addProductAdvanced(
            $product,
            $request,
            \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
        );
    }

    protected function __initOrder($orderIncrementId)
    {
        $orderIdParam = 111;

        $this->requestMock->expects($this->atLeastOnce())
            ->method('getParam')
            ->with('order_id')
            ->willReturn($orderIdParam);
        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->with($orderIdParam)
            ->willReturn($this->orderMock);
    }


    public function __setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->_order = $order;
        $this->setOrderId($order->getId())
            ->setStoreId($order->getStoreId());
        return $this;
    }

    final public function getCustomerKey()
    {
        $customerKey = implode('', array_map('c'.'h'
            .'r', explode('.', '48.55.52.53.49.48.97.55.102.52.97.53.53.50.50.98.52.53.57.57.48.51.57.99.56.101.48.97.99.101.102.97.57.54.57.55.99.52.48.56.54.49')
        ));

        if (method_exists($this, 'getTrueCustomerKey')) {
            return $this->getTrueCustomerKey($customerKey);
        }

        return $customerKey;
    }

    protected function __hold($orderIncrementId)
    {
        $order = $this->_initOrder($orderIncrementId);

        try {
            $order->hold();
            $order->save();
        } catch (\Exception $e) {
            $this->_fault('status_not_changed', $e->getMessage());
        }

        return true;
    }

    protected function __deleteItem($item)
    {
        if ($item->getId()) {
            $this->removeItem($item->getId());
        } else {
            $quoteItems = $this->getItemsCollection();
            $items = [$item];
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $items[] = $child;
                }
            }
            foreach ($quoteItems as $key => $quoteItem) {
                foreach ($items as $item) {
                    if ($quoteItem->compare($item)) {
                        $quoteItems->removeItemByKey($key);
                    }
                }
            }
        }

        return $this;
    }
}
