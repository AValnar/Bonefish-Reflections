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
 * @date       10.06.2015
 */

namespace Bonefish\Reflection\Factory;

use AValnar\Doctrine\Factory\AnnotationReaderFactory;
use Bonefish\Reflection\ReflectionService;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\Cache;

class ReflectionServiceFactory
{
    /**
     * Return an object with fully injected dependencies
     *
     * @param array $parameters
     * @return ReflectionService
     */
    public function create(array $parameters = [])
    {
        if (isset($parameters['cache']) && $parameters['cache'] instanceof Cache) {
            $cache = $parameters['cache'];
        } else {
            $cache = new ApcCache();
        }

        $annotationReaderFactory = new AnnotationReaderFactory();
        $annotationReader = $annotationReaderFactory->create($parameters);

        return new ReflectionService($cache, $annotationReader);

    }
}