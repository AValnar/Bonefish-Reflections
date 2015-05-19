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


use Bonefish\Reflection\Traits\HasDeclaringClassTrait;
use Bonefish\Reflection\Traits\NameableTrait;

class ParameterMeta
{
    use NameableTrait;
    use HasDeclaringClassTrait;

    /**
     * @var bool
     */
    protected $hasDefaultValue = FALSE;

    /**
     * @var mixed
     */
    protected $defaultValue = NULL;

    /**
     * @var bool
     */
    protected $optional;

    /**
     * @var bool
     */
    protected $allowNull;

    /**
     * @var string
     */
    protected $type = NULL;

    /**
     * @return boolean
     */
    public function hasDefaultValue()
    {
        return $this->hasDefaultValue;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->hasDefaultValue = true;
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return boolean
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * @param boolean $optional
     */
    public function setOptional($optional)
    {
        $this->optional = $optional;
    }

    /**
     * @return boolean
     */
    public function isAllowNull()
    {
        return $this->allowNull;
    }

    /**
     * @param boolean $allowNull
     */
    public function setAllowNull($allowNull)
    {
        $this->allowNull = $allowNull;
    }

    /**
     * Return type ( array or class ) if set
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


} 