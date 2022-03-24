<?php
namespace Magecomp\Ordercomment\Api\Data;

interface Ordercommentinterface
{
    /**
     * @return string|null
     */
    public function getComment();

    /**
     * @param string $comment
     * @return null
     */
    public function setComment($comment);
}
