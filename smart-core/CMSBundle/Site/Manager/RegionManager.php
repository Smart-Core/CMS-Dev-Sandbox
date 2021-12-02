<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Manager;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use SmartCore\CMSBundle\EntitySite\Region;
use SmartCore\CMSBundle\Form\Type\RegionFormType;
use SmartCore\CMSBundle\Site\SiteContext;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class RegionManager
{
    /** @var \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository */
    protected $repository;

    public function __construct(
        private SiteContext $context,
        private EntityManagerInterface $em,
        private FormFactoryInterface $formFactory,
    ) {
        $this->repository  = $em->getRepository(Region::class);

        $this->checkForDefault();
    }

    /**
     * @return Region[]
     */
    public function all(): array
    {
        return $this->repository->findBy(['site' => $this->context->getSite()], ['position' => 'ASC', 'name' => 'ASC']);
    }

    /**
     * Проверка на область по умолчанию.
     *
     * В случае если Область 'content' существует, возвращается TRUE.
     * Если нет, то создаётся и возвращается FALSE.
     *
     * @return bool
     */
    public function checkForDefault(): bool
    {
        try {
            if (!empty($this->context->getSite())
                and empty($this->repository->findOneBy(['name' => 'content', 'site' => $this->context->getSite()])))
            {
                $this->update(new Region('content', 'Content workspace', $this->context->getSite(true)));

                return false;
            }
        } catch (TableNotFoundException $e) {
            // @todo
        }

        return true;
    }

    /**
     * @param string|null $name
     * @param string|null $descr
     *
     * @return Region
     */
    public function create($name = null, $descr = null): Region
    {
        return new Region($name, $descr, $this->context->getSite());
    }

    /**
     * Creates and returns a Form instance from the type of the form.
     *
     * @param mixed $data    The initial data for the form
     * @param array $options Options for the form
     *
     * @return FormInterface
     */
    public function createForm($data = null, array $options = []): FormInterface
    {
        return $this->formFactory->create(RegionFormType::class, $data, $options);
    }

    /**
     * @param int $id
     *
     * @return Region|null
     */
    public function get(int $id): ?Region
    {
        return $this->repository->find($id);
    }

    /**
     * @param Region $entity
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove(Region $entity): void
    {
        if ('content' === $entity->getName()) {
            return;
        }

        $this->em->remove($entity);
        $this->em->flush();
    }

    /**
     * @param Region $entity
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(Region $entity): void
    {
        $this->em->persist($entity->getSite());
        $this->em->flush();

        $this->em->persist($entity);
        $this->em->flush();
    }
}
