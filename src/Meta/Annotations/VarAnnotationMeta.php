<?php
/**
 * Created by PhpStorm.
 * User: E8400
 * Date: 19.05.2015
 * Time: 17:51
 */

namespace Bonefish\Reflection\Meta\Annotations;

use Bonefish\Reflection\Meta\AnnotationMeta;

class VarAnnotationMeta extends AnnotationMeta
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }


}