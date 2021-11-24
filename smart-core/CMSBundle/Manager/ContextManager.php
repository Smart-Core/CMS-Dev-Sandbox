<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Manager;

use Doctrine\DBAL\Exception\TableNotFoundException;
//use FOS\UserBundle\Model\UserManagerInterface;
use SmartCore\CMSBundle\Cache\CmsCacheProvider;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\Twig\RegionRenderHelper;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;

class ContextManager
{
    use ContainerAwareTrait;

    protected Site $site;

    protected $current_folder_id    = 1;
    protected $current_folder_path  = '/';
    protected $current_node_id      = null;
    //protected $domain               = null;
    protected $stopwatch = null;
    protected $template             = 'default';
    protected $rendered_regions     = [];

    /** @var UserManagerInterface|null */
    protected $userManager          = null;

    /** @var CmsCacheProvider */
    protected $cache;

    public function __construct(ContainerInterface $container, Stopwatch $stopwatch = null)
    {
        $this->stopwatch   = $stopwatch;
        $this->container   = $container;
        //$this->userManager = $userManager;
        //$this->cache       = $container->get('cms.cache');

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $siteRepository = $em->getRepository('CMSBundle:Site');

        $request = $container->get('request_stack')->getMasterRequest();

        $hostname = null;

        if ($request instanceof Request) {
            $hostname = $request->server->get('HTTP_HOST');

            $func = 'idn_to_ascii';
            if (strpos($hostname, 'xn--') !== false) {
                $func = 'idn_to_utf8';
            }

            $hostname = $func($hostname, 0, INTL_IDNA_VARIANT_UTS46);

            $this->setCurrentFolderPath($request->getBasePath().'/');
        }

        $cache_key = md5('context-site-by-hostname='.$hostname);

        /**
         * Сущность сайта достаётся из кеша, по этому если надо произвести связи с сайтом, надо достать персистную сущность из БД
         */
        if (null === $this->site = $this->cache->get($cache_key)) {
            $domain = $em->getRepository('CMSBundle:Domain')->findOneBy(['name' => $hostname, 'is_enabled' => true]);

            if ($domain) {
                if ($domain->getParent()) { // Alias
                    $this->site = $siteRepository->findOneBy(['domain' => $domain->getParent()]);
                } else {
                    $this->site = $siteRepository->findOneBy(['domain' => $domain]);
                }
            }

            if (empty($this->site)) {
                try {
                    $this->site = $siteRepository->findOneBy([], ['id' => 'ASC']);
                } catch (TableNotFoundException $e) {
                    //echo "!!! Table 'cms_sites' Not Found.";
                }
            }

            $this->cache->set($cache_key, $this->site, ['site', 'domain']);
        }
    }

    /**
     * @return array
     */
    public function getSiteSwitcher(): array
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $siteSwitcher = [];
        $sites = $em->getRepository('CMSBundle:Site')->findBy(['is_enabled' => true], ['position' => 'ASC', 'name' => 'ASC']);

        if ($this->container->hasParameter('cms_sites_domains')) {
            $rewriteSiteDomains = $this->container->getParameter('cms_sites_domains');
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

    /**
     * @param int $current_folder_id
     *
     * @return $this
     */
    public function setCurrentFolderId(int $current_folder_id): ContextManager
    {
        $this->current_folder_id = $current_folder_id;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentFolderId(): int
    {
        return $this->current_folder_id;
    }

    /**
     * @param string $current_folder_path
     *
     * @return $this
     */
    public function setCurrentFolderPath(string $current_folder_path): ContextManager
    {
        $this->current_folder_path = $current_folder_path;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentFolderPath(): string
    {
        return $this->current_folder_path;
    }

    /**
     * @param int $current_node_id
     *
     * @return $this
     */
    public function setCurrentNodeId(?int $current_node_id): ContextManager
    {
        $this->current_node_id = $current_node_id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCurrentNodeId(): ?int
    {
        return $this->current_node_id;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate(string $template): ContextManager
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return UserManagerInterface|null
     */
    public function getUserManager(): ?UserManagerInterface
    {
        return $this->userManager;
    }

    /**
     * @return Site|null
     */
    public function getSite(bool $force = false): ?Site
    {
        if ($force and $this->site instanceof Site) {
            $em = $this->container->get('doctrine.orm.entity_manager');

            if ( ! $em->contains($this->site)) {
                $site = $em->find(Site::class, $this->site->getId());

                $this->site = $site;
            }

        }

        return $this->site;
    }

    /**
     * @return int|null
     */
    public function getSiteId(): ?int
    {
        return $this->site ? $this->site->getId() : null;
    }

    /**
     * @param $name
     */
    public function stopwatchStart($name): void
    {
        if ($this->stopwatch) {
            $this->stopwatch->start($name);
        }
    }

    /**
     * @param $name
     *
     * @return StopwatchEvent|null
     */
    public function stopwatchStop($name): ?StopwatchEvent
    {
        if ($this->stopwatch) {
            return $this->stopwatch->stop($name);
        }

        return null;
    }

    /**
     * @return array
     */
    public function getRenderedRegions(): array
    {
        return $this->rendered_regions;
    }

    /**
     * @param string $name
     *
     * @return Response[]|RegionRenderHelper|array
     */
    public function getRenderedRegion(string $name)
    {
        if (isset($this->rendered_regions[$name])) {
            return $this->rendered_regions[$name];
        } else {
            // @todo случае отсутствия региона - вывод в профайлер.
            return [new Response('')];
        }
    }

    /**
     * @param array $rendered_regions
     *
     * @return $this
     */
    public function setRenderedRegions($rendered_regions): self
    {
        $this->rendered_regions = $rendered_regions;

        return $this;
    }

    /**
     * @param string|null $key
     *
     * @return string|array
     */
    public function getConfigDesign(?string $key = null)
    {
        if ($key) {
            return $this->container->getParameter('cms.design')[$key];
        } else {
            return $this->container->getParameter('cms.design');
        }
    }
}
