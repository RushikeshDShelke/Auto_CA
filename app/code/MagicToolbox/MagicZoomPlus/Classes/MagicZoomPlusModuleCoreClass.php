<?php

namespace MagicToolbox\MagicZoomPlus\Classes;

/**
 * MagicZoomPlusModuleCoreClass
 *
 */
class MagicZoomPlusModuleCoreClass
{

    /**
     * MagicToolboxParamsClass class
     *
     * @var \MagicToolbox\MagicZoomPlus\Classes\MagicToolboxParamsClass
     *
     */
    public $params;

    /**
     * Tool type
     *
     * @var   string
     *
     */
    public $type = 'standard';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->params = new MagicToolboxParamsClass();
        $this->params->setScope('magiczoomplus');
        $this->params->setMapping([
            'zoomWidth' => ['0' => 'auto'],
            'zoomHeight' => ['0' => 'auto'],
            'expandCaption' => ['Yes' => 'true', 'No' => 'false'],
            'upscale' => ['Yes' => 'true', 'No' => 'false'],
            'lazyZoom' => ['Yes' => 'true', 'No' => 'false'],
            'closeOnClickOutside' => ['Yes' => 'true', 'No' => 'false'],
            'rightClick' => ['Yes' => 'true', 'No' => 'false'],
            'transitionEffect' => ['Yes' => 'true', 'No' => 'false'],
            'variableZoom' => ['Yes' => 'true', 'No' => 'false'],
            'autostart' => ['Yes' => 'true', 'No' => 'false'],
            'cssClass' => ['blurred' => '', 'dark' => 'dark-bg', 'white' => 'white-bg'],
            'smoothing' => ['Yes' => 'true', 'No' => 'false'],
        ]);
        $this->loadDefaults();
    }

    /**
     * Method to get headers string
     *
     * @param string $jsPath  Path to JS file
     * @param string $cssPath Path to CSS file
     *
     * @return string
     */
    public function getHeadersTemplate($jsPath = '', $cssPath = null)
    {
        if ($cssPath == null) {
            $cssPath = $jsPath;
        }
        $headers = [];
        $headers[] = '<!-- Magic Zoom Plus Magento 2 module version v1.5.50 [v1.6.87:v5.3.3] -->';
        $headers[] = '<script type="text/javascript">window["mgctlbx$Pltm"] = "Magento 2";</script>';
        $headers[] = '<link type="text/css" href="' . $cssPath . '/magiczoomplus.css" rel="stylesheet" media="screen" />';
        $headers[] = '<link type="text/css" href="' . $cssPath . '/magiczoomplus.module.css" rel="stylesheet" media="screen" />';
        $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magiczoomplus.js"></script>';
        $headers[] = '<script type="text/javascript" src="' . $jsPath . '/magictoolbox.utils.js"></script>';
        $headers[] = $this->getOptionsTemplate();
        return "\r\n" . implode("\r\n", $headers) . "\r\n";
    }

    /**
     * Method to get options string
     *
     * @return string
     */
    public function getOptionsTemplate()
    {
        $autostart = $this->params->getValue('autostart');//NOTE: true | false
        if ($autostart !== null) {
            $autostart = "\n\t\t'autostart':" . $autostart . ',';
        } else {
            $autostart = '';
        }
        return "<script type=\"text/javascript\">\n\tvar mzOptions = {{$autostart}\n\t\t" . $this->params->serialize(true, ",\n\t\t") . "\n\t}\n</script>\n" .
               "<script type=\"text/javascript\">\n\tvar mzMobileOptions = {" .
               "\n\t\t'zoomMode':'" . str_replace('\'', '\\\'', $this->params->getValue('zoomModeForMobile')) . "'," .
               "\n\t\t'textHoverZoomHint':'" . str_replace('\'', '\\\'', $this->params->getValue('textHoverZoomHintForMobile')) . "'," .
               "\n\t\t'textClickZoomHint':'" . str_replace('\'', '\\\'', $this->params->getValue('textClickZoomHintForMobile')) . "'," .
               "\n\t\t'textExpandHint':'" . str_replace('\'', '\\\'', $this->params->getValue('textExpandHintForMobile')) . "'" .
               "\n\t}\n</script>";
    }

    /**
     * Method to get main image HTML
     *
     * @param array $params Params
     *
     * @return string
     */
    public function getMainTemplate($params)
    {
        $img = '';
        $thumb = '';
        $thumb2x = '';
        $id = '';
        $alt = '';
        $title = '';
        $width = '';
        $height = '';
        $link = '';
        $group = '';//data-gallery

        extract($params);

        if (empty($img)) {
            return false;
        }
        if (empty($thumb)) {
            $thumb = $img;
        }
        if (empty($id)) {
            $id = hash('md5', $img, false);
        }

        if (!empty($title)) {
            $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
            if (empty($alt)) {
                $alt = $title;
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
            $title = " title=\"{$title}\"";
        } else {
            $title = '';
            if (empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
        }

        if (empty($width)) {
            $width = '';
        } else {
            $width = " width=\"{$width}\"";
        }
        if (empty($height)) {
            $height = '';
        } else {
            $height = " height=\"{$height}\"";
        }

        if ($this->params->checkValue('show-message', 'Yes')) {
            $message = '<div class="MagicToolboxMessage">' . $this->params->getValue('message') . '</div>';
        } else {
            $message = '';
        }

        if (empty($link)) {
            $link = '';
        } else {
            $link = " data-link=\"{$link}\"";
        }

        if (empty($group)) {
            $group = '';
        } else {
            $group = " data-gallery=\"{$group}\"";
        }

        $options = $this->params->serialize();

        if (!empty($options)) {
            $options = " data-options=\"{$options}\"";
        }

        $mobileOptions = [
            'zoomModeForMobile'          => 'zoomMode',
            'textHoverZoomHintForMobile' => 'textHoverZoomHint',
            'textClickZoomHintForMobile' => 'textClickZoomHint',
            'textExpandHintForMobile'    => 'textExpandHint',
        ];
        $profile = $this->params->getProfile();
        foreach ($mobileOptions as $mId => $option) {
            if (!$this->params->paramExists($mId, $profile) || $this->params->checkValue($mId, $this->params->getValue($mId, $this->params->generalProfile), $profile)) {
                $mobileOptions[$mId] = '';
                continue;
            }
            $mobileOptions[$mId] = "{$option}:" . str_replace('"', '&quot;', $this->params->getValue($mId, $profile)) . ';';
        }
        $mobileOptions = implode('', $mobileOptions);
        if (!empty($mobileOptions)) {
            $options .= " data-mobile-options=\"{$mobileOptions}\"";
        }

        if (!empty($thumb2x)) {
            //NOTICE: temporary disabled because of issue with zoom images (when the picture size is not big enough)
            //$dataImage2x = ' data-zoom-image-2x="' . $img . '" data-image-2x="' . $thumb2x . '" ';
            $dataImage2x = ' data-image-2x="' . $thumb2x . '" ';
            //$thumb2x = ' srcset="' . $thumb2x . ' 2x"';
            //$thumb2x = ' srcset="' . $thumb . ' 1x, ' . $thumb2x . ' 2x"';
            $thumb2x = ' srcset="' . str_replace(' ', '%20', $thumb) . ' 1x, ' . str_replace(' ', '%20', $thumb2x) . ' 2x"';
        } else {
            $dataImage2x = '';
        }

        return "<a id=\"MagicZoomPlusImage{$id}\" {$dataImage2x} class=\"MagicZoom\" href=\"{$img}\"{$group}{$link}{$title}{$options}><img class=\"no-sirv-lazy-load\" itemprop=\"image\" src=\"{$thumb}\" {$thumb2x}  alt=\"{$alt}\"{$width}{$height} /></a>{$message}";
    }

    /**
     * Method to get selectors HTML
     *
     * @param array $params Params
     *
     * @return string
     */
    public function getSelectorTemplate($params)
    {
        $img = '';
        $medium = '';
        $medium2x = '';
        $thumb = '';
        $thumb2x = '';
        $id = '';
        $alt = '';
        $title = '';
        $width = '';
        $height = '';

        extract($params);

        if (empty($img)) {
            return false;
        }
        if (empty($medium)) {
            $medium = $img;
        }
        if (empty($thumb)) {
            $thumb = $img;
        }

        if (empty($id)) {
            $id = hash('md5', $img, false);
        }

        if (!empty($title)) {
            $title = htmlspecialchars(htmlspecialchars_decode($title, ENT_QUOTES));
            if (empty($alt)) {
                $alt = $title;
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
            $title = " title=\"{$title}\"";
        } else {
            $title = '';
            if (empty($alt)) {
                $alt = '';
            } else {
                $alt = htmlspecialchars(htmlspecialchars_decode($alt, ENT_QUOTES));
            }
        }

        if (empty($width)) {
            $width = '';
        } else {
            $width = " width=\"{$width}\"";
        }
        if (empty($height)) {
            $height = '';
        } else {
            $height = " height=\"{$height}\"";
        }

        if (!empty($thumb2x)) {
            //$thumb2x = ' srcset="' . $thumb2x . ' 2x"';
            //$thumb2x = ' srcset="' . $thumb . ' 1x, ' . $thumb2x . ' 2x"';
            $thumb2x = ' srcset="' . str_replace(' ', '%20', $thumb) . ' 1x, ' . str_replace(' ', '%20', $thumb2x) . ' 2x"';
        }

        if (!empty($medium2x)) {
            //NOTICE: temporary disabled because of issue with zoom images (when the picture size is not big enough)
            //$medium2x = ' data-zoom-image-2x="' . $img . '" data-image-2x="' . $medium2x . '" ';
            $medium2x = ' data-image-2x="' . $medium2x . '" ';
        }

        return "<a data-zoom-id=\"MagicZoomPlusImage{$id}\" href=\"{$img}\" {$medium2x} data-image=\"{$medium}\"{$title}><img src=\"{$thumb}\" {$thumb2x} alt=\"{$alt}\"{$width}{$height} /></a>";
    }

    /**
     * Method to load defaults options
     *
     * @return void
     */
    public function loadDefaults()
    {
        $params = [
            "enable-effect"=>["id"=>"enable-effect","group"=>"General","order"=>"10","default"=>"Yes","label"=>"Enable Magic Zoom Plus","type"=>"array","subType"=>"select","values"=>["Yes","No"],"scope"=>"module"],
            "template"=>["id"=>"template","group"=>"General","order"=>"20","default"=>"bottom","label"=>"Thumbnail layout","type"=>"array","subType"=>"select","values"=>["bottom","left","right","top"],"scope"=>"module"],
            "include-headers-on-all-pages"=>["id"=>"include-headers-on-all-pages","group"=>"General","order"=>"21","default"=>"No","label"=>"Include headers on all pages","description"=>"To be able to apply effect on any CMS page.","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"module"],
            "magicscroll"=>["id"=>"magicscroll","group"=>"General","order"=>"22","default"=>"No","label"=>"Scroll thumbnails","description"=>"Powered by the versatile <a target=\"_blank\" href=\"https://marketplace.magento.com/magictoolbox-magicscroll.html\">Magic Scroll</a>. Buy Magic Scroll and upload the magicscroll.js file to your server to remove 'Magic Scroll trial version' message.","type"=>"array","subType"=>"select","values"=>["Yes","No"],"scope"=>"module"],
            "thumb-max-width"=>["id"=>"thumb-max-width","group"=>"Positioning and Geometry","order"=>"10","default"=>"550","label"=>"Maximum width of thumbnail (in pixels)","type"=>"num","scope"=>"module"],
            "thumb-max-height"=>["id"=>"thumb-max-height","group"=>"Positioning and Geometry","order"=>"11","default"=>"550","label"=>"Maximum height of thumbnail (in pixels)","type"=>"num","scope"=>"module"],
            "zoomWidth"=>["id"=>"zoomWidth","group"=>"Positioning and Geometry","order"=>"20","default"=>"auto","label"=>"Width of zoom window","description"=>"pixels or percentage, e.g. 400 or 100%.","type"=>"text","scope"=>"magiczoomplus"],
            "zoomHeight"=>["id"=>"zoomHeight","group"=>"Positioning and Geometry","order"=>"30","default"=>"auto","label"=>"Height of zoom window","description"=>"pixels or percentage, e.g. 400 or 100%.","type"=>"text","scope"=>"magiczoomplus"],
            "zoomPosition"=>["id"=>"zoomPosition","group"=>"Positioning and Geometry","order"=>"40","default"=>"right","label"=>"Position of zoom window","type"=>"array","subType"=>"radio","values"=>["top","right","bottom","left","inner"],"scope"=>"magiczoomplus"],
            "square-images"=>["id"=>"square-images","group"=>"Positioning and Geometry","order"=>"40","default"=>"No","label"=>"Always create square images","description"=>"","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"module"],
            "zoomDistance"=>["id"=>"zoomDistance","group"=>"Positioning and Geometry","order"=>"50","default"=>"15","label"=>"Zoom distance","description"=>"Distance between small image and zoom window (in pixels).","type"=>"num","scope"=>"magiczoomplus"],
            "selectorTrigger"=>["id"=>"selectorTrigger","advanced"=>"1","group"=>"Multiple images","order"=>"10","default"=>"click","label"=>"Swap trigger","description"=>"Mouse event used to switch between multiple images.","type"=>"array","subType"=>"radio","values"=>["click","hover"],"scope"=>"magiczoomplus","desktop-only"=>""],
            "selector-max-width"=>["id"=>"selector-max-width","group"=>"Multiple images","order"=>"10","default"=>"100","label"=>"Maximum width of additional thumbnails (in pixels)","type"=>"num","scope"=>"module"],
            "selector-max-height"=>["id"=>"selector-max-height","group"=>"Multiple images","order"=>"11","default"=>"100","label"=>"Maximum height of additional thumbnails (in pixels)","type"=>"num","scope"=>"module"],
            "transitionEffect"=>["id"=>"transitionEffect","advanced"=>"1","group"=>"Multiple images","order"=>"20","default"=>"Yes","label"=>"Transition effect on swap","description"=>"Whether to enable dissolve effect when switching between images.","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus"],
            "lazyZoom"=>["id"=>"lazyZoom","group"=>"Miscellaneous","order"=>"10","default"=>"No","label"=>"Lazy load of zoom image","description"=>"Whether to load large image on demand (on first activation).","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus"],
            "rightClick"=>["id"=>"rightClick","group"=>"Miscellaneous","order"=>"20","default"=>"No","label"=>"Right-click menu on image","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus","desktop-only"=>""],
            "cssClass"=>["id"=>"cssClass","advanced"=>"1","group"=>"Miscellaneous","order"=>"30","default"=>"","label"=>"Extra CSS","description"=>"Extra CSS class(es) to apply to zoom instance.","type"=>"text","scope"=>"magiczoomplus"],
            "link-to-product-page"=>["id"=>"link-to-product-page","group"=>"Miscellaneous","order"=>"30","default"=>"Yes","label"=>"Link to the product page","description"=>"Link image (when expand mode is disabled) or caption in the expand view to the product page.","type"=>"array","subType"=>"select","values"=>["Yes","No"],"scope"=>"module"],
            "show-message"=>["id"=>"show-message","group"=>"Miscellaneous","order"=>"370","default"=>"No","label"=>"Show message under images","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"module"],
            "message"=>["id"=>"message","group"=>"Miscellaneous","order"=>"380","default"=>"Move your mouse over image or click to enlarge","label"=>"Enter message to appear under images","type"=>"text","scope"=>"module"],
            "zoomMode"=>["id"=>"zoomMode","group"=>"Zoom mode","order"=>"10","default"=>"zoom","label"=>"Zoom mode","description"=>"How to zoom image. off - disable zoom.","type"=>"array","subType"=>"radio","values"=>["zoom","magnifier","preview","off"],"scope"=>"magiczoomplus","desktop-only"=>"preview"],
            "zoomOn"=>["id"=>"zoomOn","group"=>"Zoom mode","order"=>"20","default"=>"hover","label"=>"Zoom on","description"=>"When to activate zoom.","type"=>"array","subType"=>"radio","values"=>["hover","click"],"scope"=>"magiczoomplus","desktop-only"=>""],
            "upscale"=>["id"=>"upscale","advanced"=>"1","group"=>"Zoom mode","order"=>"30","default"=>"Yes","label"=>"Upscale image","description"=>"Whether to scale up the large image if its original size is not enough for a zoom effect.","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus"],
            "smoothing"=>["id"=>"smoothing","advanced"=>"1","group"=>"Zoom mode","order"=>"35","default"=>"Yes","label"=>"Smooth zoom movement","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus"],
            "variableZoom"=>["id"=>"variableZoom","advanced"=>"1","group"=>"Zoom mode","order"=>"40","default"=>"No","label"=>"Variable zoom","description"=>"Whether to allow changing zoom ratio with mouse wheel.","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus","desktop-only"=>""],
            "zoomCaption"=>["id"=>"zoomCaption","group"=>"Zoom mode","order"=>"50","default"=>"off","label"=>"Caption in zoom window","description"=>"Position of caption on zoomed image. off - disable caption on zoom window.","type"=>"array","subType"=>"radio","values"=>["top","bottom","off"],"scope"=>"magiczoomplus"],
            "expand"=>["id"=>"expand","group"=>"Expand mode","order"=>"10","default"=>"window","label"=>"Expand mode","description"=>"How to show expanded view. off - disable expanded view.","type"=>"array","subType"=>"radio","values"=>["window","fullscreen","off"],"scope"=>"magiczoomplus"],
            "expandZoomMode"=>["id"=>"expandZoomMode","group"=>"Expand mode","order"=>"20","default"=>"zoom","label"=>"Expand zoom mode","description"=>"How to zoom image in expanded view. off - disable zoom in expanded view.","type"=>"array","subType"=>"radio","values"=>["zoom","magnifier","off"],"scope"=>"magiczoomplus"],
            "expandZoomOn"=>["id"=>"expandZoomOn","group"=>"Expand mode","order"=>"21","default"=>"click","label"=>"Expand zoom on","description"=>"When and how activate zoom in expanded view. ‘always’ - zoom automatically activates upon entering the expanded view and remains active.","type"=>"array","subType"=>"radio","values"=>["click","always"],"scope"=>"magiczoomplus"],
            "expandCaption"=>["id"=>"expandCaption","group"=>"Expand mode","order"=>"30","default"=>"Yes","label"=>"Show caption in expand window","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus","desktop-only"=>""],
            "closeOnClickOutside"=>["id"=>"closeOnClickOutside","group"=>"Expand mode","order"=>"40","default"=>"Yes","label"=>"Close expanded image on click outside","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magiczoomplus"],
            "hint"=>["id"=>"hint","group"=>"Hint","order"=>"10","default"=>"once","label"=>"Display hint to suggest image is zoomable","description"=>"How to show hint. off - disable hint.","type"=>"array","subType"=>"radio","values"=>["once","always","off"],"scope"=>"magiczoomplus"],
            "textHoverZoomHint"=>["id"=>"textHoverZoomHint","advanced"=>"1","group"=>"Hint","order"=>"20","default"=>"Hover to zoom","label"=>"Hint to suggest image is zoomable (on hover)","description"=>"Hint that shows when zoom mode is enabled, but inactive, and zoom activates on hover (Zoom on: hover).","type"=>"text","scope"=>"magiczoomplus"],
            "textClickZoomHint"=>["id"=>"textClickZoomHint","advanced"=>"1","group"=>"Hint","order"=>"21","default"=>"Click to zoom","label"=>"Hint to suggest image is zoomable (on click)","description"=>"Hint that shows when zoom mode is enabled, but inactive, and zoom activates on click (Zoom on: click).","type"=>"text","scope"=>"magiczoomplus"],
            "textExpandHint"=>["id"=>"textExpandHint","advanced"=>"1","group"=>"Hint","order"=>"30","default"=>"Click to expand","label"=>"Hint to suggest image is expandable","description"=>"Hint that shows when zoom mode activated, or in inactive state if zoom mode is disabled.","type"=>"text","scope"=>"magiczoomplus"],
            "textBtnClose"=>["id"=>"textBtnClose","group"=>"Hint","order"=>"40","default"=>"Close","label"=>"Hint for “close” button","description"=>"Text label that appears on mouse over the “close” button in expanded view.","type"=>"text","scope"=>"magiczoomplus","desktop-only"=>""],
            "textBtnNext"=>["id"=>"textBtnNext","group"=>"Hint","order"=>"50","default"=>"Next","label"=>"Hint for “next” button","description"=>"Text label that appears on mouse over the “next” button arrow in expanded view.","type"=>"text","scope"=>"magiczoomplus","desktop-only"=>""],
            "textBtnPrev"=>["id"=>"textBtnPrev","group"=>"Hint","order"=>"60","default"=>"Previous","label"=>"Hint for “previous” button","description"=>"Text label that appears on mouse over the “previous” button arrow in expanded view.","type"=>"text","scope"=>"magiczoomplus","desktop-only"=>""],
            "zoomModeForMobile"=>["id"=>"zoomModeForMobile","group"=>"Mobile","order"=>"10","default"=>"off","label"=>"Zoom mode","description"=>"How to zoom image. off - disable zoom.","type"=>"array","subType"=>"radio","values"=>["zoom","magnifier","off"],"scope"=>"magiczoomplus-mobile"],
            "textHoverZoomHintForMobile"=>["id"=>"textHoverZoomHintForMobile","advanced"=>"1","group"=>"Mobile","order"=>"20","default"=>"Touch to zoom","label"=>"Hint to suggest image is zoomable (on hover)","description"=>"Hint that shows when zoom mode is enabled, but inactive, and zoom activates on hover (Zoom on: hover).","type"=>"text","scope"=>"magiczoomplus-mobile"],
            "textClickZoomHintForMobile"=>["id"=>"textClickZoomHintForMobile","advanced"=>"1","group"=>"Mobile","order"=>"21","default"=>"Double tap or pinch to zoom","label"=>"Hint to suggest image is zoomable (on click)","description"=>"Hint that shows when zoom mode is enabled, but inactive, and zoom activates on click (Zoom on: click).","type"=>"text","scope"=>"magiczoomplus-mobile"],
            "textExpandHintForMobile"=>["id"=>"textExpandHintForMobile","advanced"=>"1","group"=>"Mobile","order"=>"30","default"=>"Tap to expand","label"=>"Hint to suggest image is expandable","description"=>"Hint that shows when zoom mode activated, or in inactive state if zoom mode is disabled.","type"=>"text","scope"=>"magiczoomplus-mobile"],
            "width"=>["id"=>"width","group"=>"Scroll","order"=>"10","default"=>"auto","label"=>"Scroll width","description"=>"auto | pixels | percetage","type"=>"text","scope"=>"magicscroll"],
            "height"=>["id"=>"height","group"=>"Scroll","order"=>"20","default"=>"auto","label"=>"Scroll height","description"=>"auto | pixels | percetage","type"=>"text","scope"=>"magicscroll"],
            "orientation"=>["id"=>"orientation","group"=>"Scroll","order"=>"30","default"=>"horizontal","label"=>"Orientation of scroll","type"=>"array","subType"=>"radio","values"=>["horizontal","vertical"],"scope"=>"magicscroll"],
            "mode"=>["id"=>"mode","group"=>"Scroll","order"=>"40","default"=>"scroll","label"=>"Scroll mode","type"=>"array","subType"=>"radio","values"=>["scroll","animation","carousel","cover-flow"],"scope"=>"magicscroll"],
            "items"=>["id"=>"items","group"=>"Scroll","order"=>"50","default"=>"3","label"=>"Items to show","description"=>"auto | fit | integer | array","type"=>"text","scope"=>"magicscroll"],
            "speed"=>["id"=>"speed","group"=>"Scroll","order"=>"60","default"=>"600","label"=>"Scroll speed (in milliseconds)","description"=>"e.g. 5000 = 5 seconds","type"=>"num","scope"=>"magicscroll"],
            "autoplay"=>["id"=>"autoplay","group"=>"Scroll","order"=>"70","default"=>"0","label"=>"Autoplay speed (in milliseconds)","description"=>"e.g. 0 = disable autoplay; 600 = 0.6 seconds","type"=>"num","scope"=>"magicscroll"],
            "loop"=>["id"=>"loop","group"=>"Scroll","order"=>"80","advanced"=>"1","default"=>"infinite","label"=>"Continue scroll after the last(first) image","description"=>"infinite - scroll in loop; rewind - rewind to the first image; off - stop on the last image","type"=>"array","subType"=>"radio","values"=>["infinite","rewind","off"],"scope"=>"magicscroll"],
            "step"=>["id"=>"step","group"=>"Scroll","order"=>"90","default"=>"auto","label"=>"Number of items to scroll","description"=>"auto | integer","type"=>"text","scope"=>"magicscroll"],
            "arrows"=>["id"=>"arrows","group"=>"Scroll","order"=>"100","default"=>"inside","label"=>"Prev/Next arrows","type"=>"array","subType"=>"radio","values"=>["inside","outside","off"],"scope"=>"magicscroll"],
            "pagination"=>["id"=>"pagination","group"=>"Scroll","order"=>"110","advanced"=>"1","default"=>"No","label"=>"Show pagination (bullets)","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magicscroll"],
            "easing"=>["id"=>"easing","group"=>"Scroll","order"=>"120","advanced"=>"1","default"=>"cubic-bezier(.8, 0, .5, 1)","label"=>"CSS3 Animation Easing","description"=>"see cubic-bezier.com","type"=>"text","scope"=>"magicscroll"],
            "scrollOnWheel"=>["id"=>"scrollOnWheel","group"=>"Scroll","order"=>"130","advanced"=>"1","default"=>"auto","label"=>"Scroll On Wheel mode","description"=>"auto - automatically turn off scrolling on mouse wheel in the 'scroll' and 'animation' modes, and enable it in 'carousel' and 'cover-flow' modes","type"=>"array","subType"=>"radio","values"=>["auto","turn on","turn off"],"scope"=>"magicscroll"],
            "lazy-load"=>["id"=>"lazy-load","group"=>"Scroll","order"=>"140","advanced"=>"1","default"=>"No","label"=>"Lazy load","description"=>"Delay image loading. Images outside of view will be loaded on demand.","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"magicscroll"],
            "scroll-extra-styles"=>["id"=>"scroll-extra-styles","group"=>"Scroll","order"=>"150","advanced"=>"1","default"=>"","label"=>"Scroll extra styles","description"=>"mcs-rounded | mcs-shadows | bg-arrows | mcs-border","type"=>"text","scope"=>"module"],
            "show-image-title"=>["id"=>"show-image-title","group"=>"Scroll","order"=>"160","default"=>"No","label"=>"Show image title","type"=>"array","subType"=>"radio","values"=>["Yes","No"],"scope"=>"module"]
        ];
        $this->params->appendParams($params);
    }
}
