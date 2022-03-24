<?php

/**
 * Olegnax MegaMenu
 *
 * This file is part of Olegnax/Core.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_MegaMenu
 * @copyright   Copyright (c) 2019 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\MegaMenu\Block\Html;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Block\Html\Topmenu;
use Olegnax\MegaMenu\Helper\Cache;

class Megamenu extends Topmenu
{
    /**
     * @var string
     */
    const BASE_IMAGE_PATH = "catalog/category/";
    /**
     * @var string
     */
    private $_mediaUrl;

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->isEnabled()) {
            return 'Olegnax_MegaMenu::megamenu.phtml';
        }
        return $this->_template;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return (bool)$this->getValueOption('enable_megamenu');
    }

    /**
     * @param $path
     * @param string $default
     * @return mixed|string
     */
    public function getValueOption($path, $default = '')
    {
        if ($this->hasData($path)) {
            return $this->getData($path);
        }
        $value = $this->getConfig($path);
        if (is_null($value)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * @param string $path
     * @param string $storeCode
     * @return mixed
     */
    public function getConfig($path, $storeCode = null)
    {
        return $this->getSystemValue('ox_megamenu_settings/general/' . $path, $storeCode);
    }

    /**
     * @param string $path
     * @param string $storeCode
     * @return mixed
     */
    public function getSystemValue($path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     * @since 100.1.0
     */
    public function getCacheKeyInfo()
    {
        $keyInfo = parent::getCacheKeyInfo();
        $keyInfo[] = $this->getUrl('*/*/*', ['_current' => true, '_query' => '']);
        return $keyInfo;
    }

    /**
     * @param Node $item
     * @param $outermostClassCode
     * @param $childrenWrapClass
     * @param $limit
     * @param $colBrakes
     * @param bool $is_megamenu
     * @param int $childLevel
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getHtml_lvl0(
        Node $item,
        $outermostClassCode,
        $childrenWrapClass,
        $limit,
        $colBrakes,
        $is_megamenu = false,
        $childLevel = 0
    ) {
        $html = $this->getCacheHtml($item, $is_megamenu, $childLevel);
        $menu_style = $this->getSystemValue('athlete2_settings/header/menu_hover_style');
        $category = $this->getCategory($item);
        $is_megamenu = (bool)$this->getCatData($category, 'ox_nav_type');
        $item->setData('is_megamenu', $is_megamenu);
        $this->set_custom_class($item, $category);
        if (empty($html)) {
            $hasChildren = $item->hasChildren();
            $navContent = [];

            $style = [];
            $content = $this->getCatData($category, 'ox_bg_image');
            if (!empty($content)) {
                $style['background-image'] = 'url(' . $this->getModuleMediaUrl($content) . ')';
            }
            $style = $this->prepareStyle($style);
            $style = $style ? ' style="' . $style . '"' : '';

            $html = '';
            if ($is_megamenu) {
                $navContent = array_filter([
                    'top' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_top')),
                    'left' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_left')),
                    'right' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_right')),
                    'bottom' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_btm')),
                ]);
                $columns = $this->getCatData($category, 'ox_columns');
                if ($hasChildren || !empty($navContent)) {

                    $html .= '<div class="ox-megamenu__dropdown"' . $this->prepareAttributes([
                            'data-ox-mm-w' => $this->getCatData($category, 'ox_menu_width'),
                            'data-ox-mm-cw' => $this->getCatData($category, 'ox_nav_column_width'),
                            'data-ox-mm-col' => $this->getCatData($category, 'ox_columns'),
                        ]) . $style . '>';
                    $layout = $this->getCatData($category, 'ox_layout');
                    switch ($layout) {
                        case 2:
                            $html .= '<div class="row">';
                            if (isset($navContent['left'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                            }
                            $html .= '<div class="ox-menu-col">';
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu__categories">';
                                if ($columns > 0) {
                                    $columns = 'row ox-megamenu-list--columns-' . $columns;
                                }
                                $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                $html .= '</div>';
                            }
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                            $html .= '</div>'; //close column
                            if (isset($navContent['right'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                            }
                            $html .= '</div>'; //close row
                            break;
                        case 3:
                            $html .= '<div class="row">';
                            if (isset($navContent['left'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                            }
                            $html .= '<div class="ox-menu-col">';
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu__categories">';
                                $columns = $this->getCatData($category, 'ox_columns');
                                if ($columns > 0) {
                                    $columns = 'row ox-megamenu-list--columns-' . $columns;
                                }
                                $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                $html .= '</div>';
                            }
                            $html .= '</div>'; // close column
                            if (isset($navContent['right'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                            }
                            $html .= '</div>'; // close row
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                            break;
                        case 4:
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            $html .= '<div class="row">';
                            if (isset($navContent['left'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                            }
                            $html .= '<div class="ox-menu-col">';
                            if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu__categories">';
                                $columns = $this->getCatData($category, 'ox_columns');
                                if ($columns > 0) {
                                    $columns = 'row ox-megamenu-list--columns-' . $columns;
                                }
                                $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                $html .= '</div>';
                            }
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                            $html .= '</div>'; // close column
                            if (isset($navContent['right'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                            }
                            $html .= '</div>'; // close row

                            break;
                        default:
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            if (($hasChildren && !$this->getCatData($category,
                                        'ox_nav_subcategories')) || isset($navContent['left']) || isset($navContent['right'])) {
                                $html .= '<div class="row">';
                                if (isset($navContent['left'])) {
                                    $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                            'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                                }
                                if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                    $html .= '<div class="ox-megamenu-block ox-megamenu__categories ox-menu-col">';
                                    $columns = $this->getCatData($category, 'ox_columns');
                                    if ($columns > 0) {
                                        $columns = 'row ox-megamenu-list--columns-' . $columns;
                                    }
                                    $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                    $html .= '</div>';
                                }
                                if (isset($navContent['right'])) {
                                    $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                            'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                                }
                                $html .= '</div>'; //close row
                            }
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                    }

                    $html .= '</div>';
                }
            } else {
                if ($hasChildren) {

                    $menuWidth = $this->getCatData($category, 'ox_menu_width');
                    $menuWidth = $menuWidth ? 'data-ox-mm-w="' . $menuWidth . '"' : '';
                    $html .= '<div class="ox-megamenu__dropdown" ' . $menuWidth . $style . '><ul class="ox-megamenu-list">{SUBMENU_NEXTLEVEL}</ul></div>';
                }
            }
            $html_a = $this->getCatCLContent($category) . $this->getItemName($item) . $this->getCatLabel($category);
            if ($menu_style == 'menu-style-5') {
               $html_a .= '<span class="a2-menu-stroke"><span></span><span></span><span></span></span>';
            }
            if ($hasChildren || !empty($navContent)) {
                $html_a .= $this->add_parent_arrow($this->getConfig('show_menu_parent_arrow'));
            }
            $html_a = $this->wrapItemLink($html_a, $item,
                $category, $outermostClassCode);
            $html = $html_a . $html;
            $this->setCacheHtml($html, $item, $is_megamenu, $childLevel);
        }

        $html = str_replace(
            '{SUBMENU_NEXTLEVEL}',
            $this->_addSubMenu(
                $item,
                $childLevel,
                $childrenWrapClass,
                $limit,
                $is_megamenu
            ),
            $html
        );
        $html = '<li ' . $this->_getRenderedMenuItemAttributes($item) . '>' . $html . '</li>';

        return $html;
    }

    /**
     * @param $item
     * @param $is_megamenu
     * @param $childLevel
     * @return string
     */
    protected function getCacheHtml($item, $is_megamenu, $childLevel)
    {
        $cache_id = $this->_Cache()->getId('getMegamenuItem', [$item->getId(), $is_megamenu, $childLevel - 1]);
        $cache = $this->_Cache()->load($cache_id);
        return empty($cache) ? "" : $cache;
    }

    /**
     * @return Cache
     */
    protected function _Cache()
    {
        return $this->_loadObject(Cache::class);
    }

    /**
     * @param string $object
     * @return mixed
     */
    protected function _loadObject($object)
    {
        return $this->_getObjectManager()->get($object);
    }

    /**
     * @return ObjectManager
     */
    protected function _getObjectManager()
    {
        return ObjectManager::getInstance();
    }

    /**
     * @param Node $item
     * @return mixed
     */
    protected function getCategory($item)
    {
        $cache_id = $this->_Cache()->getId('getCategory', [$item->getId()]);

        $cache = $this->_Cache()->loadObject($cache_id);

        if (false !== $cache) {
            return $cache;
        }
        $cat = $this->_getCategory(str_replace('category-node-', '', $item->getId()));
        $data = [];
        if ($cat) {
            $data = $cat->getData();
            if (!empty($data)) {
                $this->_Cache()->save($data, $cache_id);
            }
        }

        return $data;
    }

    /**
     * @param int $id
     * @return CategoryFactory
     */
    protected function _getCategory($id)
    {
        $id = abs(intval($id));
        if (0 == $id) {
            return false;
        }
        return $this->_loadObject(CategoryFactory::class)->create()->load($id);
    }

    protected function getCatData($category, $key)
    {
        return array_key_exists($key, $category) ? $category[$key] : '';
    }

    /**
     * @param Node $item
     * @param $category
     */
    protected function set_custom_class(Node $item, $category)
    {
        $custom_class = trim($this->getCatData($category, 'ox_nav_custom_class'));
        if (!empty($custom_class)) {
            $item->setData('class', trim($item->getClass() . ' ' . $custom_class));
        }
    }

    /**
     * @param string $path
     * @return string
     * @throws NoSuchEntityException
     */
    public function getModuleMediaUrl($path = '')
    {

        return $this->getBaseMediaUrl() . $path;
    }


    /**
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getBaseMediaUrl()
    {
        if (!$this->_mediaUrl) {
            /** @var StoreManagerInterface $storeManager */
            $storeManager = $this->_loadObject(StoreManagerInterface::class);
            $this->_mediaUrl = $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .
                static::BASE_IMAGE_PATH;
        }

        return $this->_mediaUrl;
    }

    /**
     * @param array $style
     * @param string $separatorValue
     * @param string $separatorAttribute
     * @return string
     */
    public function prepareStyle(array $style, $separatorValue = ': ', $separatorAttribute = ';')
    {
        $style = array_filter($style);
        if (empty($style)) {
            return '';
        }
        foreach ($style as $key => &$value) {
            $value = $key . $separatorValue . $value;
        }
        $style = implode($separatorAttribute, $style);

        return $style;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function getBlockTemplateProcessor($content = '')
    {
        return $this->_loadObject(FilterProvider::class)->getBlockFilter()->filter(trim($content));
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function prepareAttributes(array $attributes)
    {
        $attributes = array_filter($attributes);
        if (empty($attributes)) {
            return '';
        }
        $html = '';
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= sprintf(
                ' %s="%s"',
                $attributeName,
                str_replace(
                    '"',
                    '\"',
                    $attributeValue
                )
            );
        }
        return $html;
    }

    /**
     * @param $category
     * @return string
     */
    protected function getCatCLContent($category)
    {
        $content = $this->getCatData($category, 'ox_nav_custom_link_content');
        if ($content) {
            return '<span class="ox-menu-item__custom-element">' . $content . '</span>';
        }
        return '';
    }

    /**
     * @param Node $item
     * @return string
     */
    protected function getItemName(Node $item)
    {
        return '<span class="name">' . $this->escapeHtml($item->getName()) . '</span>';
    }

    /**
     * @param $category
     * @return string
     */
    protected function getCatLabel($category)
    {
        $content = $this->getCatData($category, 'ox_category_label');
        if ($content) {
            return '<span class="ox-megamenu-label" style="' . $this->prepareStyle([
                    'color' => $this->getCatData($category, 'ox_label_text_color'),
                    'background-color' => $this->getCatData($category, 'ox_label_color')
                ]) . '">' . $content . '</span>';
        }
        return '';
    }

    /**
     * @param bool $showSubCat
     * @return string
     */
    protected function add_parent_arrow($showSubCat)
    {
        if ($showSubCat) {
            return '<i class="ox-menu-arrow"></i>';
        } else {
            return '<i class="ox-menu-arrow hide-on-desktop"></i>';
        }
    }

    /**
     * @param string $html
     * @param Node $item
     * @param $category
     * @param string $outermostClassCode
     * @return string
     */
    protected function wrapItemLink($html, Node $item, $category, $outermostClassCode)
    {
        $custom_url = trim($this->getCatData($category, 'ox_nav_custom_link'));
        $attrs = $this->prepareAttributes([
            'target' => $this->getCatData($category, 'ox_nav_custom_link_target') ? '_blank' : '',
            'href' => $custom_url ?: $item->getUrl(),
            'data-url' => $custom_url ? 'custom' : '',
            'style' => $this->prepareStyle([
                'color' => $this->getCatData($category, 'ox_title_text_color'),
                'background-color' => $this->getCatData($category, 'ox_title_bg_color'),
            ]),
        ]);

        return '<a ' . $attrs . ' ' . $outermostClassCode . '>' . $html . '</a>';
    }

    /**
     * @param $html
     * @param $item
     * @param $is_megamenu
     * @param $childLevel
     * @return bool
     */
    protected function setCacheHtml($html, $item, $is_megamenu, $childLevel)
    {
        $cache_id = $this->_Cache()->getId('getMegamenuItem', [$item->getId(), $is_megamenu, $childLevel - 1]);
        return $this->_Cache()->save($html, $cache_id);
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @param bool $is_megamenu
     *
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit, $is_megamenu = false, $is_megamenu_parent = false)
    {
        if (!$this->isEnabled()) {
            return parent::_addSubMenu($child, $childLevel, $childrenWrapClass, $limit);
        }
        if (!$child->hasChildren()) {
            return '';
        }

        $colStops = [];
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }
        $customClass = '';

        if (($is_megamenu && $is_megamenu_parent && $childLevel > 1) || (!$is_megamenu && !$is_megamenu_parent && $childLevel > 0) || ($is_megamenu && !$is_megamenu_parent && $childLevel > 2)) {
            $customClass = 'ox-submenu';
        }
        $html = $this->_getHtml($child, $childrenWrapClass, $limit, $colStops, $is_megamenu);
        if ($childLevel > 0 && !$is_megamenu) {
            $html = '<ul class="' . $customClass . ' level' . $childLevel . ' ' . $childrenWrapClass . '">' . $html . '</ul>';
        }
        if ($childLevel > 1 && $is_megamenu) {
            $html = '<ul class="' . $customClass . ' level' . $childLevel . ' ' . $childrenWrapClass . '">' . $html . '</ul>';
        }
        return $html;
    }

    /**
     * @param Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @param bool $is_megamenu
     * @return string
     */
    protected function _getHtml(
        Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = [],
        $is_megamenu = false
    ) {
        if (!$this->isEnabled()) {
            return parent::_getHtml($menuTree, $childrenWrapClass, $limit, $colBrakes);
        }
        $html = '';

        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

        $counter = 0;
        $itemPosition = 0;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();

        /** @var Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                continue;
            }
            $itemPosition++;
            $counter++;
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $currentClass = $child->getClass();

                if (empty($currentClass)) {
                    $child->setClass($outermostClass);
                } else {
                    $child->setClass($currentClass . ' ' . $outermostClass);
                }
            }

            $fucntion = '_getHtml_lvlx';
            switch ($childLevel) {
                case 0:
                    $fucntion = '_getHtml_lvl0';
                    break;
                case 1:
                    $fucntion = '_getHtml_lvl1';
                    break;
            }
            $html .= $this->$fucntion($child, $outermostClassCode, $childrenWrapClass, $limit, $colBrakes, $is_megamenu,
                $childLevel);

        }

        return $html;
    }

    /**
     * @param Node $item
     * @param $outermostClassCode
     * @param $childrenWrapClass
     * @param $limit
     * @param $colBrakes
     * @param bool $is_megamenu_parent
     * @param int $childLevel
     * @return string
     * @throws NoSuchEntityException
     */
    protected function _getHtml_lvl1(
        Node $item,
        $outermostClassCode,
        $childrenWrapClass,
        $limit,
        $colBrakes,
        $is_megamenu_parent = false,
        $childLevel = 1
    ) {

        $html = $this->getCacheHtml($item, $is_megamenu_parent, $childLevel);
        $category = $this->getCategory($item);
        $is_megamenu = (bool)$this->getCatData($category, 'ox_nav_type');
        $item->setData('is_megamenu', $is_megamenu);
        $this->set_custom_class($item, $category);
        if (empty($html)) {
            $hasChildren = $item->hasChildren();
            $navContent = [];

            $style = [];
            $content = $this->getCatData($category, 'ox_bg_image');
            $cat_img = $this->getCatData($category, 'ox_cat_image');

            if (!empty($content)) {
                $style['background-image'] = 'url(' . $this->getModuleMediaUrl($content) . ')';
            }
            $style = $this->prepareStyle($style);
            $style = $style ? ' style="' . $style . '"' : '';

            $html = '';
            if ($is_megamenu) {
                $navContent = array_filter([
                    'top' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_top')),
                    'left' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_left')),
                    'right' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_right')),
                    'bottom' => $this->getBlockTemplateProcessor($this->getCatData($category, 'ox_nav_btm')),
                ]);
                $columns = $this->getCatData($category, 'ox_columns');
                if ($hasChildren || !empty($navContent)) {

                    $html .= '<div class="ox-megamenu__dropdown"' . $this->prepareAttributes([
                            'data-ox-mm-w' => $this->getCatData($category, 'ox_menu_width'),
                            'data-ox-mm-cw' => $this->getCatData($category, 'ox_nav_column_width'),
                            'data-ox-mm-col' => $this->getCatData($category, 'ox_columns'),
                        ]) . $style . '>';
                    $layout = $this->getCatData($category, 'ox_layout');
                    switch ($layout) {
                        case 2:
                            $html .= '<div class="row">';
                            if (isset($navContent['left'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                            }
                            $html .= '<div class="ox-menu-col">';
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu__categories">';
                                if ($columns > 0) {
                                    $columns = 'row ox-megamenu-list--columns-' . $columns;
                                }
                                $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                $html .= '</div>';
                            }
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                            $html .= '</div>'; //close column
                            if (isset($navContent['right'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                            }
                            $html .= '</div>'; //close row
                            break;
                        case 3:
                            $html .= '<div class="row">';
                            if (isset($navContent['left'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                            }
                            $html .= '<div class="ox-menu-col">';
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu__categories">';
                                $columns = $this->getCatData($category, 'ox_columns');
                                if ($columns > 0) {
                                    $columns = 'row ox-megamenu-list--columns-' . $columns;
                                }
                                $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                $html .= '</div>';
                            }
                            $html .= '</div>'; // close column
                            if (isset($navContent['right'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                            }
                            $html .= '</div>'; // close row
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                            break;
                        case 4:
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            $html .= '<div class="row">';
                            if (isset($navContent['left'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                            }
                            $html .= '<div class="ox-menu-col">';
                            if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu__categories">';
                                $columns = $this->getCatData($category, 'ox_columns');
                                if ($columns > 0) {
                                    $columns = 'row ox-megamenu-list--columns-' . $columns;
                                }
                                $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                $html .= '</div>';
                            }
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                            $html .= '</div>'; // close column
                            if (isset($navContent['right'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                        'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                            }
                            $html .= '</div>'; // close row

                            break;
                        default:
                            if (isset($navContent['top'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-top">' . $navContent['top'] . '</div>';
                            }
                            if (($hasChildren && !$this->getCatData($category,
                                        'ox_nav_subcategories')) || isset($navContent['left']) || isset($navContent['right'])) {
                                $html .= '<div class="row">';
                                if (isset($navContent['left'])) {
                                    $html .= '<div class="ox-megamenu-block ox-megamenu-block-left ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                            'ox_nav_left_width') . '">' . $navContent['left'] . '</div>';
                                }
                                if ($hasChildren && !$this->getCatData($category, 'ox_nav_subcategories')) {
                                    $html .= '<div class="ox-megamenu-block ox-megamenu__categories ox-menu-col">';
                                    $columns = $this->getCatData($category, 'ox_columns');
                                    if ($columns > 0) {
                                        $columns = 'row ox-megamenu-list--columns-' . $columns;
                                    }
                                    $html .= '<ul class="ox-megamenu-list ' . $columns . '">{SUBMENU_NEXTLEVEL}</ul>';
                                    $html .= '</div>';
                                }
                                if (isset($navContent['right'])) {
                                    $html .= '<div class="ox-megamenu-block ox-megamenu-block-right ox-menu-col ox-menu-col-' . $this->getCatData($category,
                                            'ox_nav_right_width') . '">' . $navContent['right'] . '</div>';
                                }
                                $html .= '</div>'; //close row
                            }
                            if (isset($navContent['bottom'])) {
                                $html .= '<div class="ox-megamenu-block ox-megamenu-block-bottom">' . $navContent['bottom'] . '</div>';
                            }
                    }

                    $html .= '</div>';
                }
            } else {
                $html .= '{SUBMENU_NEXTLEVEL}';
            }

            $html_a = $this->getCatCLContent($category);
            if (!empty($cat_img)) {
                $html_a .= '<span class="ox-menu__category-image"><img src="' . $this->getModuleMediaUrl($cat_img) . '" alt="' . $this->escapeHtml($item->getName()) .'"></span>';
            }
            $html_a .= $this->getItemName($item) . $this->getCatLabel($category);
            if ($hasChildren || !empty($navContent)) {
                $html_a .= $this->add_parent_arrow($this->getConfig('show_sub_parent_arrow'));
            }
            $html_a = $this->wrapItemLink($html_a, $item,
                $category, $outermostClassCode);
            $html = $html_a . $html;
            $this->setCacheHtml($html, $item, $is_megamenu, $childLevel);
        }

        $html = str_replace(
            '{SUBMENU_NEXTLEVEL}',
            $this->_addSubMenu(
                $item,
                $childLevel,
                $childrenWrapClass,
                $limit,
                $is_megamenu,
                $is_megamenu_parent
            ),
            $html
        );
        $html = '<li ' . $this->_getRenderedMenuItemAttributes($item) . '>' . $html . '</li>';

        return $html;
    }

    /**
     * @param Node $item
     * @param string $outermostClassCode
     * @param string $childrenWrapClass
     * @param int $limit
     * @param string $colBrakes
     * @param bool $is_megamenu
     * @param int $childLevel
     * @return string
     */
    protected function _getHtml_lvlx(
        Node $item,
        $outermostClassCode,
        $childrenWrapClass,
        $limit,
        $colBrakes,
        $is_megamenu = false,
        $childLevel = 2
    ) {
        $html = $this->getCacheHtml($item, $is_megamenu, $childLevel);
        $category = $this->getCategory($item);
        $this->set_custom_class($item, $category);
        if (empty($html)) {

            $html = $this->getCatCLContent($category) . $this->getItemName($item);

            if ($item->hasChildren()) {
                $html .= $this->add_parent_arrow($this->getConfig('show_sub_parent_arrow'));
            }
            $html .= $this->getCatLabel($category);
            $html = $this->wrapItemLink(
                $html,
                $item,
                $category,
                $outermostClassCode
            );
            $this->setCacheHtml($html, $item, $is_megamenu, $childLevel);
        }

        $html = '<li ' . $this->_getRenderedMenuItemAttributes($item) . '>' . $html . $this->_addSubMenu(
                $item,
                $childLevel,
                $childrenWrapClass,
                $limit,
                $is_megamenu
            ) . '</li>';

        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(Node $item)
    {
        if (!$this->isEnabled()) {
            return parent::_getMenuItemAttributes($item);
        }
        $menuItemClasses = $this->_getMenuItemClasses($item);
        $menuItemAttributes = ['class' => implode(' ', $menuItemClasses)];
        $category = $this->getCategory($item);
        if ($this->getCatData($category, 'ox_data_tm_align_horizontal') && 0 == $item->getLevel()) {
            $menuItemAttributes['data-ox-mm-a-h'] = $this->getCatData($category, 'ox_data_tm_align_horizontal');
        }
        return $menuItemAttributes;
    }

    /**
     * Returns array of menu item's classes
     *
     * @param Node $item
     * @return array
     */
    protected function _getMenuItemClasses(Node $item)
    {
        $classes = parent::_getMenuItemClasses($item);
        if (!$this->isEnabled()) {
            return $classes;
        }
        if (0 == $item->getLevel()) {
            $classes[] = 'ox-dropdown--' . ($item->getData('is_megamenu') ? 'megamenu' : 'simple');
        } elseif (1 == $item->getLevel() && $item->getData('is_megamenu')) {
            $classes[] = 'ox-dropdown--megamenu';
            if ($this->getCatData($this->getCategory($item), 'ox_mm_lvl2_align_vertical')) {
                $classes[] = 'ox-mm__lvl1-' . $this->getCatData($this->getCategory($item), 'ox_mm_lvl2_align_vertical');
            }
        }
        if ($item->getData('is_megamenu')) {
            $isNavContent = !empty($this->getCatData($this->getCategory($item), 'ox_nav_top')) || !empty($this->getCatData($this->getCategory($item),
                    'ox_nav_btm')) || !empty($this->getCatData($this->getCategory($item), 'ox_nav_left')) || !empty($this->getCatData($this->getCategory($item), 'ox_nav_right'));
            if ($item->hasChildren() || $isNavContent) {
                $classes[] = 'parent';
            }
        }

        return $classes;
    }

}
