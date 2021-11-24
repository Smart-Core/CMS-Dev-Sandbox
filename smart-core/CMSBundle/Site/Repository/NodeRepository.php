<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Repository;

use Doctrine\ORM\EntityRepository;
use SmartCore\CMSBundle\EntitySite\Folder;
use SmartCore\CMSBundle\EntitySite\Node;
use SmartCore\CMSBundle\EntitySite\Region;
use SmartCore\RadBundle\Doctrine\RepositoryTrait;

class NodeRepository extends EntityRepository
{
    use RepositoryTrait\FindDeleted;

    /**
     * @return Node[]
     */
    public function findIn(array $list): array
    {
        $list_string = '';
        foreach ($list as $node_id) {
            $list_string .= $node_id.',';
        }

        $list_string = substr($list_string, 0, strlen($list_string) - 1); // обрезка послезней запятой.

        if (false == $list_string) {
            return [];
        }

        return $this->_em->createQuery("
            SELECT n
            FROM CMSBundle:Node AS n
            WHERE n.id IN({$list_string})
            ORDER BY n.position ASC
        ")->getResult();
    }

    public function countInRegion(Region|int $region): int
    {
        $query = $this->_em->createQuery('
            SELECT COUNT(n.id)
            FROM CMSBundle:Node AS n
            JOIN CMSBundle:Region AS r
            WHERE r.id = :region
            AND n.region = r
        ')->setParameter('region', $region);

        return $query->getSingleScalarResult();
    }

    /**
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getInFolder(int|Folder $folder, array $exclude_nodes = [])
    {
        if ($folder instanceof Folder) {
            $folder = $folder->getId();
        }

        $nodes_table = $this->_class->getTableName();

        $sql = "
            SELECT id
            FROM $nodes_table
            WHERE folder_id = '$folder'
            AND is_active = TRUE
            AND deleted_at IS NULL
        ";

        // Исключение ранее включенных нод.
        foreach ($exclude_nodes as $node_id) {
            $sql .= " AND id != '{$node_id}'";
        }

        $sql .= ' ORDER BY position';

        return $this->_em->getConnection()->query($sql);
    }

    /**
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getInheritedInFolder(int|Folder $folder)
    {
        if ($folder instanceof Folder) {
            $folder = $folder->getId();
        }

        $nodes_table           = $this->_class->getTableName();
        $regions_inherit_table = $this->_em->getClassMetadata(Region::class)->getAssociationMapping('folders')['joinTable']['name'];

        $sql = "
            SELECT n.id
            FROM $nodes_table AS n,
                $regions_inherit_table AS ri
            WHERE n.region_id = ri.region_id
                AND n.is_active = TRUE
                AND n.deleted_at IS NULL
                AND n.folder_id = '$folder'
                AND ri.folder_id = '$folder'
            ORDER BY n.position ASC
        ";

        return $this->_em->getConnection()->query($sql);
    }
}
