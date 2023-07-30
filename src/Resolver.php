<?php

namespace Zarf;

class Resolver
{

    public function resolve($class)
    {
        $reflector = new \ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new \Exception("[$class] is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $class;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->getDependencies($parameters);

        return $reflector->newInstanceArgs($dependencies);
    }


    public function getDependencies($parameters)
    {
        $dependencies = array();
        foreach ($parameters as $index => $parameter) {

            if ($parameter->isOptional()) {
                continue;
            }
            $type = $parameter->getType();
            $class = null !== $type && !$type->isBuiltin() ? $type->getName() : null;
            if ($class) {
                $isInternal = (new \ReflectionClass($class))->isInternal();
                if (!$isInternal) {
                    $dependencies[] = $this->resolve($class);
                }
                // $parameters[$index] = new Reference($class);
            }

            // $dependency = $parameter->getClass();

            // if (is_null($dependency)) {
            //     $dependencies[] = $this->resolveNonClass($parameter);
            // } else {
            //     $dependencies[] = $this->resolve($dependency->name);
            // }
        }

        return $dependencies;
    }


    //  public function resolveNonClass(ReflectionParameter $parameter)
    // {
    //     if ($parameter->isDefaultValueAvailable()) {
    //         return $parameter->getDefaultValue();
    //     }

    //     throw new Exception("Erm.. Cannot resolve the unkown!?");
    // }
}
