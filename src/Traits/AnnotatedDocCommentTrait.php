<?php
/**
 * Copyright (C) 2015  Alexander Schmidt
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @author     Alexander Schmidt <mail@story75.com>
 * @copyright  Copyright (c) 2015, Alexander Schmidt
 * @date       13.05.2015
 */

namespace Bonefish\Reflection\Traits;

use Bonefish\Reflection\Meta\AnnotationMeta;

trait AnnotatedDocCommentTrait
{
    /**
     * @var string
     */
    protected $docComment;

    /**
     * @var AnnotationMeta[]
     */
    protected $annotations  = [];

    /**
     * @return \Bonefish\Reflection\Meta\AnnotationMeta[]
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @param string $name
     * @return AnnotationMeta|bool
     */
    public function getAnnotation($name)
    {
        return isset($this->annotations[$name]) ? $this->annotations[$name] : FALSE;
    }

    /**
     * @param \Bonefish\Reflection\Meta\AnnotationMeta[] $annotations
     * @return self
     */
    public function setAnnotations($annotations)
    {
        $this->annotations = $annotations;
        return $this;
    }

    /**
     * @param \Bonefish\Reflection\Meta\AnnotationMeta $annotation
     * @return self
     */
    public function addAnnotation(AnnotationMeta $annotation)
    {
        $this->annotations[$annotation->getName()] = $annotation;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocComment()
    {
        return $this->docComment;
    }

    /**
     * @param string $docComment
     * @return self
     */
    public function setDocComment($docComment)
    {
        $this->docComment = $docComment;
        return $this;
    }
} 