<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Manager;

use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\EntityCms\Parameter;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\Site\Entity\Folder;
use SmartCore\CMSBundle\Site\Entity\Region;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CmsManager
{
    private ObjectManager $em;

    public function __construct(
        private KernelInterface $kernel,
        private ManagerRegistry $doctrine,
        private TranslatorInterface $translator,
    ) {
        $this->em = $this->doctrine->getManager('cms');
    }

    public function getEm(): ObjectManager
    {
        return $this->em;
    }

    public function addSite(string $name, ?string $theme = null, bool $clearCache = false): Site
    {
        $site = new Site($name);
        $site->setTheme($theme);

        $this->em->persist($site);
        $this->em->flush();

        if ($clearCache) {
            $this->execCommand('cache:clear'); // Очистка кеша и тутже вармап.
        }

        return $site;
    }

    public function getSiteEm(int|string $id): ObjectManager
    {
        return $this->doctrine->getManager('site_' . $id);
    }

    public function getSite(int $id): Site
    {
        return $this->em->getRepository(Site::class)->find($id);
    }

    /**
     * @return Site[]
     */
    public function getSites(): array
    {
        try {
            return $this->em->getRepository(Site::class)->findBy([], ['id' => 'ASC']);
        } catch (TableNotFoundException) {
            return [];
        }
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

    public function getProjectKey(): string
    {
        return $this->getParameter('project_key');
    }

    public function schemaUpdate(string $em = 'cms', bool $force = true, bool $dumpSql = false): BufferedOutput
    {
        return $this->execCommand('doctrine:schema:update', [
            '--em'    => $em,
            '--force' => $force,
            '--dump-sql' => $dumpSql,
            '--no-debug' => true,
        ]);
    }

    public function execCommand(string $name, array $arguments = []): BufferedOutput
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $command = $application->find($name);

        $output = new BufferedOutput();

        $command->run(new ArrayInput($arguments), $output);

        return $output;
    }

    public function bootInit(): void
    {
        $this->execCommand('cms:adminlte:create-symlink', ['--soft' => true]);

        $this->schemaUpdate('cms');

        $this->initProjectKey();

        $languages = $this->em->getRepository(Language::class)->findAll();

        if (empty($languages)) {
            $language = new Language($this->translator->getLocale());

            $this->em->persist($language);
            $this->em->flush();
        }

        $sites = $this->getSites();

        if (empty($sites)) {
            $sites[] = $this->addSite('Default Site');
        }

        foreach ($sites as $site) {
            $siteDbName = 'site_' . $site->getId();

            $this->schemaUpdate($siteDbName);

            $emSite = $this->getSiteEm($site->getId());

            $defaultRegion = $emSite->getRepository(Region::class)->findOneBy(['name' => 'content']);

            if (!$defaultRegion) {
                $defaultRegion = new Region('content', 'Content workspace');

                $emSite->persist($defaultRegion);
                $emSite->flush();
            }

            $rootFolder = $emSite->getRepository(Folder::class)->find(1);

            if (!$rootFolder) {
                $rootFolder = new Folder();
                $rootFolder->setTitle('Homepage');

                $emSite->persist($rootFolder);
                $emSite->flush();
            }
        }
    }

    protected function initProjectKey(): void
    {
        if (!$this->hasParameter('project_key')) {
            $this->setParameter('project_key', $this->generateRandomSecret());
        }
    }

    protected function generateRandomSecret(): string
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return hash('sha1', openssl_random_pseudo_bytes(23));
        }

        return hash('sha1', uniqid((string) mt_rand(), true));
    }

    /**
     * @todo !!!
     */
    public function getSiteSwitcher(): array
    {
        $siteSwitcher = [];
        $sites = $this->em->getRepository('CMSBundle:Site')->findBy(['is_enabled' => true], ['position' => 'ASC', 'name' => 'ASC']);

        if ($this->parameterBag->has('cms_sites_domains')) {
            $rewriteSiteDomains = $this->parameterBag->get('cms_sites_domains');
        } else {
            $rewriteSiteDomains = [];
        }

        foreach ($sites as $site) {
            $siteSwitcher[$site->getId()] = [
                'id'       => $site->getId(),
                'name'     => $site->getName(),
                'domain'   => (string) $site->getDomain(),
                'selected' => $site->getId() == $this->getSite()->getId() ? true : false,
            ];

            if (isset($rewriteSiteDomains[$site->getId()]) and !empty($rewriteSiteDomains[$site->getId()])) {
                $siteSwitcher[$site->getId()]['domain'] = $rewriteSiteDomains[$site->getId()];
            }
        }

        return $siteSwitcher;
    }
}
