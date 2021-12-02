<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Entity;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;

#[ORM\Entity]
#[ORM\Table('parameters')]
class Parameter
{
    use ColumnTrait\Id;
    use ColumnTrait\NameUnique;

    #[ORM\Column(type: 'boolean')]
    protected bool $is_serialized = false;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $value = null;

    public function __construct(string $name = null, ?string $value = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }

        if ($value !== null) {
            $this->setValue($value);
        }
    }

    public function getValue(): mixed
    {
        return $this->is_serialized ? unserialize($this->value) : $this->value;
    }

    public function setValue(mixed $value): self
    {
        if (\is_iterable($value) or \is_object($value)) {
            $this->is_serialized = true;
            $this->value = serialize($value);
        } else {
            $this->is_serialized = false;
            $this->value = (string) $value;
        }

        return $this;
    }

    public function isSerialized(): bool
    {
        return $this->is_serialized;
    }
}
