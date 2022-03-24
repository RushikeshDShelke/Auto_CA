<?php

namespace Olegnax\MegaMenu\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Olegnax\MegaMenu\Model\Attribute\Alignhorizontal;
use Olegnax\MegaMenu\Model\Attribute\MmLvl2AlignVertical;
use Olegnax\MegaMenu\Model\Attribute\Columns;
use Olegnax\MegaMenu\Model\Attribute\Columnsgrid;
use Olegnax\MegaMenu\Model\Attribute\Layout;
use Olegnax\MegaMenu\Model\Attribute\Menuwidth;

class InstallData implements InstallDataInterface
{

    private $eavSetupFactory;
    private $categorySetupFactory;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory, CategorySetupFactory $categorySetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $this->_install($setup);
        $setup->endSetup();
    }

    private function _install(ModuleDataSetupInterface $setup)
    {
        $tableFields = $this->fields();
        if (!empty($tableFields)) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
            foreach ($tableFields as $entity => $fields) {
                foreach ($fields as $key => $value) {
                    if (Category::ENTITY == $entity) {
                        $categorySetup->addAttribute($entity, $key, $value);
                    } else {
                        $eavSetup->addAttribute($entity, $key, $value);
                    }
                }
            }
        }
    }

    private function fields()
    {
        return [
            Category::ENTITY => [
                'ox_title_bg_color' => [
                    'type' => 'text',
                    'label' => 'Menu Item Background color',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 10,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'This field is compatible only with 1st-level category megamenu'
                ],
                'ox_title_text_color' => [
                    'type' => 'text',
                    'label' => 'Menu Item Text color',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 12,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                ],
                'ox_nav_custom_class' => [
                    'type' => 'text',
                    'label' => 'Custom Class',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 20,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                ],
                'ox_nav_custom_link_content' => [
                    'type' => 'text',
                    'label' => 'Custom Content near Link',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 22,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'Use to show icons near menu item.'
                ],
                'ox_nav_custom_link' => [
                    'type' => 'text',
                    'label' => 'Custom Link Url',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 30,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'Override category link with your custom external link'
                ],
                'ox_nav_custom_link_target' => [
                    'type' => 'int',
                    'label' => 'Open link in a new window',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'required' => false,
                    'sort_order' => 32,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu'
                ],
                'ox_category_label' => [
                    'type' => 'text',
                    'label' => 'Label',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 40,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'Set any text, i.e.: Hot, Sale, Featured or whatever you want...'
                ],
                'ox_label_color' => [
                    'type' => 'text',
                    'label' => 'Label Background color',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 42,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                ],
                'ox_label_text_color' => [
                    'type' => 'text',
                    'label' => 'Label Text color',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 44,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                ],
                'ox_columns' => [
                    'type' => 'text',
                    'label' => 'Columns categories per row',
                    'input' => 'select',
                    'source' => Columns::class,
                    'required' => false,
                    'sort_order' => 50,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => '1'
                ],
                'ox_nav_column_width' => [
                    'type' => 'text',
                    'label' => 'Menu Column Width',
                    'input' => 'text',
                    'required' => false,
                    'sort_order' => 52,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'note' => 'Use to set simple drop width and for Menu Dropdown Width options Column Max Width',
                    'group' => 'Mega menu'
                ],
                'ox_bg_image' => [
                    'type' => 'varchar',
                    'label' => 'DropDown Background Image',
                    'input' => 'image',
                    'required' => false,
                    'sort_order' => 54,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'backend' => Image::class,
                    'group' => 'Mega menu',
                ],
                'ox_data_tm_align_horizontal' => [
                    'type' => 'text',
                    'label' => 'Top Menu Dropdown Align Horizontal',
                    'input' => 'select',
                    'source' => Alignhorizontal::class,
                    'required' => false,
                    'sort_order' => 60,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => 'menu-left',
                    'note' => 'For 1st-level category megamenu only'
                ],
                'ox_nav_type' => [
                    'type' => 'int',
                    'label' => 'Enable Mega Menu',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'required' => false,
                    'sort_order' => 70,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu'
                ],
                'ox_mm_lvl2_align_vertical' => [
                    'type' => 'text',
                    'label' => 'Mega Menu in Dropdown Behavior',
                    'input' => 'select',
                    'source' => MmLvl2AlignVertical::class,
                    'required' => false,
                    'sort_order' => 71,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => '',
                    'note' => 'For 2st-level category megamenu only'
                ],
                'ox_nav_subcategories' => [
                    'type' => 'int',
                    'label' => 'Hide subcategories',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'required' => false,
                    'sort_order' => 72,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => 0,
                ],
                'ox_menu_width' => [
                    'type' => 'text',
                    'label' => 'Top Menu Dropdown Width',
                    'input' => 'select',
                    'source' => Menuwidth::class,
                    'required' => false,
                    'sort_order' => 74,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'Columns Max Width and Custom uses value from Menu Column Width option above.',
                    'default' => ''

                ],
                'ox_cat_image' => [
                    'type' => 'varchar',
                    'label' => 'Category Image',
                    'input' => 'image',
                    'required' => false,
                    'sort_order' => 76,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'backend' => Image::class,
                    'group' => 'Mega menu',
                    'note' => 'Show image above menu item'
                ],
                'ox_layout' => [
                    'type' => 'text',
                    'label' => 'DropDown Layout',
                    'input' => 'select',
                    'source' => Layout::class,
                    'required' => false,
                    'sort_order' => 80,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => '1'
                ],
                'ox_nav_btm' => [
                    'type' => 'text',
                    'label' => 'Html block under menu ',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 82,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'For 1st-level category megamenu only'
                ],
                'ox_nav_top' => [
                    'type' => 'text',
                    'label' => 'Top Html',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 84,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'For 1st-level category megamenu only'
                ],
                'ox_nav_left' => [
                    'type' => 'text',
                    'label' => 'Left Html',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 86,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'For 1st-level category megamenu only'
                ],
                'ox_nav_left_width' => [
                    'type' => 'text',
                    'label' => 'Left Html Width',
                    'input' => 'select',
                    'source' => Columnsgrid::class,
                    'required' => false,
                    'sort_order' => 88,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => '3'
                ],
                'ox_nav_right' => [
                    'type' => 'text',
                    'label' => 'Megamenu Vertical Right Html',
                    'input' => 'textarea',
                    'required' => false,
                    'sort_order' => 90,
                    'wysiwyg_enabled' => false,
                    'is_html_allowed_on_front' => true,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'note' => 'For 1st-level category megamenu only'
                ],
                'ox_nav_right_width' => [
                    'type' => 'text',
                    'label' => 'Right Html Width',
                    'input' => 'select',
                    'source' => Columnsgrid::class,
                    'required' => false,
                    'sort_order' => 92,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Mega menu',
                    'default' => '3'
                ],
            ],
        ];
    }
}
