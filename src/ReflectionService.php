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


use Bonefish\Reflection\Meta\AnnotationMeta;
use Bonefish\Reflection\Meta\Annotations\VarAnnotationMeta;
use Bonefish\Reflection\Meta\ClassMeta;
use Bonefish\Reflection\Meta\MethodMeta;
use Bonefish\Reflection\Meta\ParameterMeta;
use Bonefish\Reflection\Meta\PropertyMeta;
use Bonefish\Reflection\Meta\UseMeta;
use Bonefish\Reflection\Traits\AnnotatedDocCommentTrait;
use Doctrine\Common\Cache\Cache;
use Nette\Reflection\AnnotationsParser;
use Nette\Reflection\ClassType;

class ReflectionService
{
    /**
     * @var array
     */
    protected $reflections = [];

    /**
     * @var array
     */
    protected $metaReflections = [];

    /**
     * @var Cache
     */
    protected $cache = null;

    const CACHE_PREFIX = 'bonefish.reflection.meta.';

    /**
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param Cache $cache
     * @return self
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @param string $className
     * @return ClassType
     */
    public function getClassReflection($className)
    {
        if (!isset($this->reflections[$className])) {
            $this->reflections[$className] = new ClassType($className);
        }

        return $this->reflections[$className];
    }

    /**
     * @param string $className
     * @return ClassMeta
     */
    public function getClassMetaReflection($className)
    {
        if (!isset($this->metaReflections[$className])) {
            $this->metaReflections[$className] = $this->buildClassMetaReflection($className);
        }

        return $this->metaReflections[$className];
    }

    /**
     * @param string $className
     * @return ClassMeta
     */
    protected function buildClassMetaReflection($className)
    {
        if ($this->cache !== null) {
            $cacheKey = $this->getCacheKey($className);
            $hit = $this->cache->fetch($cacheKey);

            if ($hit !== false) {
                return $hit;
            }
        }

        $classMeta = new ClassMeta();

        $reflection = $this->getClassReflection($className);

        $classMeta->setName($className);
        $classMeta->setInterface($reflection->isInterface());
        $classMeta->setDocComment($reflection->getDocComment());
        $inNamespace = $reflection->inNamespace();
        $classMeta->setNamespaced($inNamespace);

        if ($inNamespace) {
            $classMeta->setNamespace($reflection->getNamespaceName());
        }

        $this->createUseMeta($reflection, $classMeta);
        $this->createAnnotationMeta($reflection->getAnnotations(), $classMeta);
        $this->createPropertyMeta($reflection, $classMeta);
        $this->createMethodMeta($reflection, $classMeta);

        if ($this->cache !== null) {
            $this->cache->save($cacheKey, $classMeta);
        }

        return $classMeta;
    }

    /**
     * @param array $value
     * @return null|string|array
     */
    protected function getAnnotationProperties(array $value)
    {
        $value = $value[0];
        $parameter = null;

        if (is_string($value)) {
            $parameter = $value;
        } elseif (is_object($value) && $value instanceof \ArrayAccess) {
            foreach ($value as $key => $val) {
                $parameter[$key] = $val;
            }
        }

        return $parameter;
    }

    /**
     * @param array $annotations
     * @param ClassMeta|PropertyMeta|MethodMeta $metaClass
     */
    protected function createAnnotationMeta(array $annotations, $metaClass)
    {
        foreach ($annotations as $annotation => $value) {
            $annotationMeta = $annotation === 'var' ? new VarAnnotationMeta() : new AnnotationMeta();
            $annotationMeta->setName($annotation);

            $parameterValue = $this->getAnnotationProperties($value);

            $parameter = new ParameterMeta();
            $parameter->setDeclaringClass($metaClass->getDeclaringClass());
            if (is_string($parameterValue) && is_array($parameterValue)) {
                $parameter->setAllowNull(false);
                $parameter->setOptional(false);
                $parameter->setDefaultValue($parameterValue);
                $parameter->setHasDefaultValue(true);
                $parameter->setType(is_string($parameterValue) ? 'string' : 'array');
            } else {
                $parameter->setHasDefaultValue(false);
                $parameter->setAllowNull(true);
                $parameter->setOptional(true);
            }

            $annotationMeta->setParameter($parameter);

            if ($annotation === 'var') {
                $this->setClassNameForVarAnnotation($metaClass, $parameterValue, $annotationMeta);
            }
            $metaClass->addAnnotation($annotationMeta);
        }
    }

    protected function getCacheKey($className)
    {
        return self::CACHE_PREFIX . str_replace('\\', '.', $className);
    }

    /**
     * @param ClassType $reflection
     * @param ClassMeta $classMeta
     */
    protected function createUseMeta(ClassType $reflection, ClassMeta $classMeta)
    {
        $parsedPHP = AnnotationsParser::parsePhp(file_get_contents($reflection->getFileName()));

        if (!isset($parsedPHP[$reflection->getName()]['use'])) {
            return;
        }

        foreach ($parsedPHP[$reflection->getName()]['use'] as $alias => $class) {
            $useStatement = new UseMeta();
            $useStatement->setAlias($alias);
            $useStatement->setOriginal($class);
            $classMeta->addUseStatement($useStatement);
        }

        unset($parsedPHP);
    }

    /**
     * @param ClassType $reflection
     * @param ClassMeta $classMeta
     */
    protected function createPropertyMeta(ClassType $reflection, ClassMeta $classMeta)
    {
        foreach ($reflection->getProperties() as $propertyReflection) {
            $property = new PropertyMeta();
            $property->setName($propertyReflection->getName());
            $property->setDocComment($propertyReflection->getDocComment());
            $property->setPublic($propertyReflection->isPublic());
            $property->setPrivate($propertyReflection->isPrivate());
            $property->setProtected($propertyReflection->isProtected());

            // Set declaring class and first check against the current to avoid a recursion hell
            if ($propertyReflection->getDeclaringClass()->getName() !== $classMeta->getName()) {
                $property->setDeclaringClass($this->getClassMetaReflection($propertyReflection->getDeclaringClass()->getName()));
            } else {
                $property->setDeclaringClass($classMeta);
            }

            $this->createAnnotationMeta($propertyReflection->getAnnotations(), $property);
            $classMeta->addProperty($property);
        }
    }

    /**
     * @param ClassType $reflection
     * @param ClassMeta $classMeta
     */
    protected function createMethodMeta(ClassType $reflection, ClassMeta $classMeta)
    {
        foreach ($reflection->getMethods() as $methodReflection) {
            $method = new MethodMeta();
            $method->setName($reflection->getName());
            $method->setPublic($methodReflection->isPublic());
            $method->setPrivate($methodReflection->isPrivate());
            $method->setProtected($methodReflection->isProtected());

            // Set declaring class and first check against the current to avoid a recursion hell
            if ($methodReflection->getDeclaringClass()->getName() !== $classMeta->getName()) {
                $method->setDeclaringClass($this->getClassMetaReflection($methodReflection->getDeclaringClass()->getName()));
            } else {
                $method->setDeclaringClass($classMeta);
            }

            $this->createParameterMeta($methodReflection->getParameters(), $method);

            $this->createAnnotationMeta($methodReflection->getAnnotations(), $method);
            $classMeta->addMethod($method);
        }
    }

    /**
     * @param \ReflectionParameter[] $parameters
     * @param MethodMeta $metaClass
     */
    protected function createParameterMeta(array $parameters, $metaClass)
    {
        foreach ($parameters as $parameterReflection) {

            $parameter = new ParameterMeta();
            $parameter->setDeclaringClass($metaClass->getDeclaringClass());
            $parameter->setName($parameterReflection->getName());
            $parameter->setOptional($parameterReflection->isOptional());
            if ($parameterReflection->isDefaultValueAvailable()) {
                $parameter->setDefaultValue($parameterReflection->getDefaultValue());
            }
            $parameter->setAllowNull($parameterReflection->allowsNull());
            if ($parameterReflection->isArray()) {
                $parameter->setType('array');
            } elseif ($parameterReflection->getClass() !== null) {
                $parameter->setType($parameterReflection->getClass()->getName());
            } else {
                $parameter->setType('mixed');
            }

            $metaClass->addParameter($parameter);
        }
    }

    /**
     * @param PropertyMeta $metaClass
     * @param string $parameter
     * @param VarAnnotationMeta $annotationMeta
     */
    protected function setClassNameForVarAnnotation(
        PropertyMeta $metaClass,
        $parameter,
        VarAnnotationMeta $annotationMeta
    ) {
        $className = $parameter;

        if ($className[0] !== '\\' && $className !== 'array') {
            $declaringClass = $metaClass->getDeclaringClass();
            $useStatement = $declaringClass->getUseStatement($parameter);
            if ($useStatement !== false) {
                $className = '\\' . $useStatement->getOriginal();
            } elseif ($declaringClass->isNamespaced()) {
                $className = '\\' . $declaringClass->getNamespace() . '\\' . $className;
            }
        }

        $annotationMeta->setClassName($className);
    }
}