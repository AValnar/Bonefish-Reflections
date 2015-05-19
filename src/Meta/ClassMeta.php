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
use Bonefish\Reflection\Traits\NameableTrait;

class ClassMeta
{
    use AnnotatedDocCommentTrait;
    use NameableTrait;

    /**
     * @var PropertyMeta[]
     */
    protected $properties = [];

    /**
     * @var UseMeta[]
     */
    protected $useStatements  = [];

    /**
     * @var MethodMeta[]
     */
    protected $methods  = [];

    /**
     * @var bool
     */
    protected $interface;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var bool
     */
    protected $namespaced;

    /**
     * @return boolean
     */
    public function isNamespaced()
    {
        return $this->namespaced;
    }

    /**
     * @param boolean $namespaced
     */
    public function setNamespaced($namespaced)
    {
        $this->namespaced = $namespaced;
    }

    /**
     * @return boolean
     */
    public function isInterface()
    {
        return $this->interface;
    }

    /**
     * @param boolean $interface
     */
    public function setInterface($interface)
    {
        $this->interface = $interface;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return PropertyMeta[]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * @return PropertyMeta|bool
     */
    public function getProperty($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : FALSE;
    }

    /**
     * @param PropertyMeta[] $properties
     * @return self
     */
    public function setProperties($properties)
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @param PropertyMeta $property
     * @return self
     */
    public function addProperty(PropertyMeta $property)
    {
        $this->properties[$property->getName()] = $property;
        return $this;
    }

    /**
     * @return UseMeta[]
     */
    public function getUseStatements()
    {
        return $this->useStatements;
    }

    /**
     * @param string $name
     * @return UseMeta|bool
     */
    public function getUseStatement($name)
    {
        return isset($this->useStatements[$name]) ? $this->useStatements[$name] : FALSE;
    }

    /**
     * @param UseMeta[] $useStatements
     * @return self
     */
    public function setUseStatements($useStatements)
    {
        $this->useStatements = $useStatements;
        return $this;
    }

    /**
     * @param UseMeta $useStatement
     * @return self
     */
    public function addUseStatement($useStatement)
    {
        $this->useStatements[$useStatement->getAlias()] = $useStatement;
        return $this;
    }

    /**
     * @return MethodMeta[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param MethodMeta[] $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * @param MethodMeta $method
     * @return self
     */
    public function addMethod($method)
    {
        $this->methods[$method->getName()] = $method;
        return $this;
    }

    /**
     * @param string $name
     * @return MethodMeta
     */
    public function getMethod($name)
    {
        return isset($this->methods[$name]) ? $this->methods[$name] : FALSE;
    }

} 