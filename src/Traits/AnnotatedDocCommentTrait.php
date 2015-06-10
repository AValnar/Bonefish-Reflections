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

trait AnnotatedDocCommentTrait
{
    /**
     * @var string
     */
    protected $docComment;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $annotations  = [];

    /**
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * @param string $name
     * @return object|bool
     */
    public function getAnnotation($name)
    {
        return isset($this->annotations[$name]) ? $this->annotations[$name] : FALSE;
    }

    /**
     * @param array $annotations
     * @return self
     */
    public function setAnnotations($annotations)
    {
        $this->annotations = $annotations;
        return $this;
    }

    /**
     * @param object $annotation
     * @return self
     */
    public function addAnnotation($annotation)
    {
        $this->annotations[get_class($annotation)] = $annotation;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
        $description = preg_replace('#^\s*\*\s?#ms', '', trim($docComment, '/*'));
        $description = preg_split('#^\s*(?=@[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF-\\\]*)#m', $description, 2);

        if (isset($description[0])) {
            $this->description = $description[0];
        }

        $this->docComment = $docComment;
        return $this;
    }
} 