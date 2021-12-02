<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Traits;

trait ParametersTrait
{
    private array $parameters = [];

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function hasParameter(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    public function setParameter(string $key, mixed $value): self
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }
}
