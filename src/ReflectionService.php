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


use Bonefish\Reflection\Annotations\Variable;
use Bonefish\Reflection\Meta\ClassMeta;
use Bonefish\Reflection\Meta\MethodMeta;
use Bonefish\Reflection\Meta\ParameterMeta;
use Bonefish\Reflection\Meta\PropertyMeta;
use Bonefish\Reflection\Meta\UseMeta;
use Bonefish\Traits\CacheHelperTrait;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\Cache;
use Nette\Reflection\AnnotationsParser;
use Nette\Reflection\ClassType;

class ReflectionService
{
    use CacheHelperTrait;

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
    protected $cache;

    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @param Cache $cache
     * @param Reader $annotationReader
     */
    public function __construct(Cache $cache, Reader $annotationReader)
    {
        $this->cache = $cache;
        $this->annotationReader = $annotationReader;
        $this->setCachePrefix('bonefish.reflection.meta');
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
        $cacheKey = $this->getCacheKey($className);
        $hit = $this->cache->fetch($cacheKey);

        if ($hit !== false) {
            return $hit;
        }

        $classMeta = new ClassMeta();

        $reflection = $this->getClassReflection($className);

        $classMeta->setName($className);
        $classMeta->setShortName($reflection->getShortName());
        $classMeta->setInterface($reflection->isInterface());
        $classMeta->setDocComment($reflection->getDocComment());
        $inNamespace = $reflection->inNamespace();
        $classMeta->setNamespaced($inNamespace);
        $parentClass = $reflection->getParentClass() === null ? null : $this->getClassMetaReflection($reflection->getParentClass()->getName());
        $classMeta->setParentClass($parentClass);

        foreach($reflection->getInterfaceNames() as $interface) {
            $classMeta->addInterface($this->getClassMetaReflection($interface));
        }

        if ($inNamespace) {
            $classMeta->setNamespace($reflection->getNamespaceName());
        }

        $annotations = $this->annotationReader->getClassAnnotations($reflection);
        $this->createAnnotationMeta($classMeta, $annotations);

        $this->createUseMeta($reflection, $classMeta);
        $this->createPropertyMeta($reflection, $classMeta);
        $this->createMethodMeta($reflection, $classMeta);

        $this->cache->save($cacheKey, $classMeta);

        return $classMeta;
    }

    /**
     * @param array $annotations
     * @param ClassMeta|MethodMeta|PropertyMeta $metaClass
     */
    protected function createAnnotationMeta($metaClass, ...$annotations)
    {
        foreach ($annotations as $annotation) {
            $metaClass->addAnnotation($annotation);
        }
    }

    /**
     * @param ClassType $reflection
     * @param ClassMeta $classMeta
     */
    protected function createUseMeta(ClassType $reflection, ClassMeta $classMeta)
    {
        if (!$reflection->getFileName()) return;

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

            $annotations = $this->annotationReader->getPropertyAnnotations($propertyReflection);

            if ($varAnnotation = $propertyReflection->getAnnotation('var'))
            {
                $annotations[] = new Variable($this->getClassNameForVarAnnotation($property, $varAnnotation));
            }

            $this->createAnnotationMeta($property, $annotations);
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
            $method->setName($methodReflection->getName());
            $method->setPublic($methodReflection->isPublic());
            $method->setPrivate($methodReflection->isPrivate());
            $method->setProtected($methodReflection->isProtected());
            $method->setDocComment($methodReflection->getDocComment());

            // Set declaring class and first check against the current to avoid a recursion hell
            if ($methodReflection->getDeclaringClass()->getName() !== $classMeta->getName()) {
                $method->setDeclaringClass($this->getClassMetaReflection($methodReflection->getDeclaringClass()->getName()));
            } else {
                $method->setDeclaringClass($classMeta);
            }

            $this->createParameterMeta($methodReflection->getParameters(), $method);

            $annotations = $this->annotationReader->getMethodAnnotations($methodReflection);
            $this->createAnnotationMeta($method, $annotations);

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
     * @param string $type
     * @return string
     */
    protected function getClassNameForVarAnnotation(
        PropertyMeta $metaClass,
        $type
    ) {
        $className = $type;

        if ($className[0] !== '\\' && $className !== 'array') {
            $declaringClass = $metaClass->getDeclaringClass();
            $useStatement = $declaringClass->getUseStatement($type);
            if ($useStatement !== false) {
                $className = '\\' . $useStatement->getOriginal();
            } elseif ($declaringClass->isNamespaced()) {
                $className = '\\' . $declaringClass->getNamespace() . '\\' . $className;
            }
        }

        return $className;
    }
}