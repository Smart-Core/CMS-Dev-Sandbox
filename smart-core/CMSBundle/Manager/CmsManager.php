<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Parameter;
use SmartCore\CMSBundle\EntityCms\Site;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class CmsManager
{
    private ObjectManager $em;

    public function __construct(
        private KernelInterface $kernel,
        private ManagerRegistry $doctrine,
    ) {
        $this->em = $this->doctrine->getManager('cms');
    }

    public function addSite(string $name, ?string $theme = null)
    {
        $site = new Site($name);
        $site->setTheme($theme);

        $this->em->persist($site);
        $this->em->flush();

        // @todo DB for site
    }

    /**
     * @return Site[]
     */
    public function getSites(): array
    {
        return $this->em->getRepository(Site::class)->findBy([], ['id' => 'ASC']);
    }

    /**
     * @return Domain[]
     */
    public function getDomains(): array
    {
        return $this->em->getRepository(Domain::class)->findBy([], ['id' => 'ASC']);
    }

    public function allParameters(): array
    {
        return $this->em->getRepository(Parameter::class)->findBy([], ['name' => 'ASC']);
    }

    public function getParameter(string $name, mixed $default = null): mixed
    {
        /** @var Parameter $param */
        $param = $this->em->getRepository(Parameter::class)->findOneBy(['name' => $name]);

        if ($param !== null) {
            return $param->getValue();
        }

        return $default;
    }

    public function hasParameter(string $name): bool
    {
        /** @var Parameter $param */
        $param = $this->em->getRepository(Parameter::class)->findOneBy(['name' => $name]);

        return $param === null ? false : true;
    }

    public function setParameter(string $name, mixed $value): self
    {
        /** @var Parameter $param */
        $param = $this->em->getRepository(Parameter::class)->findOneBy(['name' => $name]);

        if ($param === null) {
            $param = new Parameter($name);
        }

        $param->setValue($value);

        $this->em->persist($param);
        $this->em->flush();

        return $this;
    }

    public function schemaUpdate(string $em = 'cms', bool $force = true, bool $dumpSql = false): BufferedOutput
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $command = $application->find('doctrine:schema:update');
        $arguments = [
            '--em'    => $em,
            '--force' => $force,
            '--dump-sql' => $dumpSql,
        ];

        $output = new BufferedOutput();

        $command->run(new ArrayInput($arguments), $output);

        return $output;
    }

    public function getProjectKey(): string
    {
        return $this->getParameter('project_key');
    }
}
