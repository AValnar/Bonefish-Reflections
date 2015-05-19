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

namespace Bonefish\Reflection;


use Bonefish\Reflection\Meta\ClassMeta;

class ClassNameResolver
{
    /**
     * @var ReflectionService
     */
    public $reflectionService;

    /**
     * @var array
     */
    protected $interfaceImplementations = [];

    /**
     * @param string $className
     * @param ClassMeta $reflector
     * @return string
     */
    public function resolveClassName($className, ClassMeta $reflector = NULL)
    {
        // Check for fully qualified
        if ($className[0] === '\\') {
            return $className;
        }

        // Add \ if no reflector given since we don't care then
        if ($reflector === NULL) {
            return '\\' . $className;
        }

        // Check if reflector has use statement for class
        $useStatement = $reflector->getUseStatement($className);
        if ($useStatement !== FALSE) {
            return '\\' . $useStatement->getOriginal();
        }

        // Check if reflector is namespaced
        if ($reflector->isNamespaced())
        {
            return '\\' . $reflector->getNamespace() . '\\' . $className;
        }

        return $className;
    }

} 