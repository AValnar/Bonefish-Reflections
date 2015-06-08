<?php

namespace Bonefish\Reflection;

/**
 * Copyright (C) 2014  Alexander Schmidt
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
 * @copyright  Copyright (c) 2014, Alexander Schmidt
 * @version    1.0
 * @date       2014-09-21
 * @package Bonefish\Reflection
 * @deprecated
 */
class Helper
{
    /**
     * @param string $suffix
     * @param \ReflectionClass $reflection
     * @return \ReflectionMethod[]
     */
    public function getSuffixMethods($suffix, \ReflectionClass $reflection)
    {
        return $this->getRegExMethods('/([a-zA-Z]*)' . $suffix . '/', $reflection);
    }

    /**
     * @param $prefix
     * @param \ReflectionClass $reflection
     * @return \ReflectionMethod[]
     */
    public function getPrefixMethods($prefix, \ReflectionClass $reflection)
    {
        return $this->getRegExMethods('/' . $prefix . '([a-zA-Z]*)/', $reflection);
    }

    /**
     * @param string $regEx
     * @param \ReflectionClass $reflection
     * @return \ReflectionMethod[]
     */
    public function getRegExMethods($regEx, \ReflectionClass $reflection)
    {
        $return = [];

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            preg_match($regEx, $method->getName(), $match);
            if (isset($match[1])) {
                $return[] = $method;
            }
        }

        return $return;
    }
} 