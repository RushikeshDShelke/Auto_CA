<?php

namespace IntegerNet\Categorydisplay\Test\Integration\Controller;

class NewProductsCategoryTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public static function loadFixture()
    {
        include __DIR__ . '/../_files/categories.php';
        include __DIR__ . '/../_files/products.php';
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadFixture
     */
    public function testCategoryDisplaysCorrectProducts()
    {
        $this->dispatch('catalog/category/view/id/3');

        $this->assertContains('Simple Product 1', $this->getResponse()->getBody());
        $this->assertNotContains('Simple Product 2', $this->getResponse()->getBody());
    }
}
