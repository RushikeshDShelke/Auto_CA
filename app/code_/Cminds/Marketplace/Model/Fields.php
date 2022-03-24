<?php

namespace Cminds\Marketplace\Model;

class Fields extends \Magento\Framework\Model\AbstractModel
{
    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const IS_WYSIWYG = 1;
    const IS_NOT_WYSIWYG = 0;

    /**
     * Construct.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cminds\Marketplace\Model\ResourceModel\Fields');
    }

    /**
     * Execute before save actions.
     *
     * @return Fields
     */
    public function beforeSave()
    {
        $this->handleWysiwygProperty();

        return parent::beforeSave();
    }

    /**
     * Handle Wysiwyg property before Field is saved.
     *
     * @return Fields
     */
    private function handleWysiwygProperty()
    {
        if ($this->getIsWysiwyg() === static::IS_NOT_WYSIWYG) {
            return $this;
        }

        if (!$this->canWysiwyg()) {
            $this->setIsWysiwyg(static::IS_NOT_WYSIWYG);
        }

        return $this;
    }

    /**
     * Check if custom Supplier Profile Field can be wysiwyg.
     *
     * @return bool
     */
    private function canWysiwyg()
    {
        if ($this->getType() === static::TYPE_TEXTAREA) {
            return true;
        }

        return false;
    }
}
