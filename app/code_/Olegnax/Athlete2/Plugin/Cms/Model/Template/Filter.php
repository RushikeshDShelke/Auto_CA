<?php


namespace Olegnax\Athlete2\Plugin\Cms\Model\Template;


use Olegnax\Athlete2\Helper\LazyLoad;

class Filter
{

    /**
     * @var LazyLoad
     */
    protected $lazyLoad;

    public function __construct(LazyLoad $lazyLoad)
    {
        $this->lazyLoad = $lazyLoad;
    }

    public function afterFilter($subject, $result)
    {
        return $this->lazyLoad->replaceImageToLazy($result);
    }

}