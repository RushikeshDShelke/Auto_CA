<?php

namespace Cminds\Marketplace\Block\Adminhtml\Customer\Edit\Tab\Grid;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\DataObject;
use Cminds\Marketplace\Model\ResourceModel\Categories\CollectionFactory as RestrictedCategoryCollectionFactory;
use Cminds\Marketplace\Model\ResourceModel\Categories\Collection as RestrictedCategoryCollection;

class CategoriesCheckbox extends AbstractRenderer
{
    private $categoryCollectionFactory;
    private $restrictedCategoryCollectionFactory;
    private $restrictedCategoryIds;

    public function __construct(
        Context $context,
        CategoryCollectionFactory $categoryCollectionFactory,
        RestrictedCategoryCollectionFactory $restrictedCategoryCollectionFactory,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->restrictedCategoryCollectionFactory = $restrictedCategoryCollectionFactory;
    }

    public function render(DataObject $row)
    {
        $categoryId = (int)$row->getId();
        $restrictedCategoryIds = $this->getRestrictedCategoryIds();

        $checked = in_array($categoryId, $restrictedCategoryIds, true)
            ? ''
            : 'checked="checked"';

        $html = sprintf(
            '<label class="data-grid-checkbox-cell-inner" for="category_ids_%s">',
            $categoryId
        );

        $html .= sprintf(
            '<input type="hidden" name="%s" value="%s"/>',
            'category_ids[' . $categoryId . ']',
            $categoryId
        );

        $html .= sprintf(
            '<input type="checkbox" name="%s" value="%s" id="category_ids_%s" %s '
            . 'class="categories_checkbox checkbox admin__control-checkbox"'
            . 'data-form-part="customer_form"/>',
            'category_ids[' . $categoryId . ']',
            $categoryId,
            $categoryId,
            $checked
        );

        $html .= sprintf(
            '<label for="category_ids_%s"></label>',
            $categoryId
        );

        $html .= '</label>';

        return $html;

        /*$selectedCategories = $this->getSelectedCategories();
        $selectedAllCategories = $this->getAllCategories();

        $value = $row->getData($this->getColumn()->getIndex());
        $data_form_part = '';
        if ($this->getColumn()->getFieldName() == 'categories_ids[]') {
            $id = 'categories_ids';
            $name = 'categories_ids[' . $value . ']';
            $checked = '';
            if (in_array($value, $selectedCategories)) {
                $checked = ' checked ';
                $data_form_part = "data-form-part = 'customer_form'";
            }

        } else {
            $id = 'categories_all_ids';
            $name = 'all_categories_ids[' . $value . ']';

            $checked = '';
            if (in_array($value, $selectedAllCategories)) {
                $checked = ' checked ';
                $data_form_part = "data-form-part = 'customer_form'";
            }

        }

        $html = '<label class="data-grid-checkbox-cell-inner" ';
        $html .= ' for="id_' . $this->escapeHtml($value) . '_' . $id . '">';
        $html .= '<input type="checkbox" ';
        $html .= 'name="' . $name . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" ';
        $html .= $checked;
        $html .= $data_form_part;
        $html .= 'id="id_' . $this->escapeHtml($value) . '_' . $id . '" ';
        $html .= 'class="categories_checkbox ' .
            ($this->getColumn()->getInlineCss()
                ? $this->getColumn()->getInlineCss()
                : 'checkbox'
            ) .
            ' admin__control-checkbox' . '"';
        $html .= '/>';
        $html .= '<label for="id_' . $this->escapeHtml($value)
            . '_' . $id . '"></label>';
        $html .= '</label>';

        return $html;*/
    }

    private function getRestrictedCategoryIds()
    {
        $supplierId = $this->getRequest()->getParam('id');

        if ($this->restrictedCategoryIds === null) {
            /** @var RestrictedCategoryCollection $collection */
            $collection = $this->restrictedCategoryCollectionFactory->create();
            $collection->addFilter('supplier_id', $supplierId);

            $this->restrictedCategoryIds = [];
            foreach ($collection as $restrictedCategory) {
                $categoryId = (int)$restrictedCategory->getCategoryId();
                if (in_array($categoryId, $this->restrictedCategoryIds, true)) {
                    continue;
                }

                $this->restrictedCategoryIds[] = $categoryId;
            }
        }

        return $this->restrictedCategoryIds;
    }
}
