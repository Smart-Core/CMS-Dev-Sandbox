<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Doctrine\Sqlite;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType as BaseJsonType;

class JsonType extends BaseJsonType
{
    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'json';
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
