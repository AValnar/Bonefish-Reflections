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

namespace Bonefish\Reflection\Meta;


use Bonefish\Reflection\Traits\AnnotatedDocCommentTrait;
use Bonefish\Reflection\Traits\HasDeclaringClassTrait;
use Bonefish\Reflection\Traits\NameableTrait;
use Bonefish\Reflection\Traits\VisibilityTrait;

class MethodMeta
{
    use AnnotatedDocCommentTrait;
    use VisibilityTrait;
    use HasDeclaringClassTrait;
    use NameableTrait;

    /**
     * @var ParameterMeta[]
     */
    protected $parameters = [];

    /**
     * @return ParameterMeta[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param ParameterMeta[] $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param string $name
     * @return ParameterMeta|bool
     */
    public function getParameter($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : FALSE;
    }

    /**
     * @param ParameterMeta $parameter
     * @return self
     */
    public function addParameter(ParameterMeta $parameter)
    {
        $this->parameters[$parameter->getName()] = $parameter;
        return $this;
    }

} 