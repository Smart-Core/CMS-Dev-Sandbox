<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Manager;

use Doctrine\Persistence\ObjectManager;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\Twig\RegionRenderHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ContextManager
{
    protected Site $site;
    protected ObjectManager $siteEm;

    //protected $domain               = null;
    protected int $current_folder_id    = 1;
    protected string $current_path      = '/';
    protected ?int $current_node_id     = null;
    protected string $template          = 'default';
    protected array $rendered_regions   = [];

    public function __construct(
        protected CmsManager $cmsManager,
        RequestStack $requestStack,
    ) {
        $this->siteEm = $this->cmsManager->getSiteEm(1); // @todo !!! dynamic sites
        $this->site = $this->cmsManager->getSite(1);

        $hostname = null;
        $request = $requestStack->getMainRequest();
        if ($request instanceof Request) {
            $hostname = $request->server->get('HTTP_HOST');

            $func = 'idn_to_ascii';
            if (strpos($hostname, 'xn--') !== false) {
                $func = 'idn_to_utf8';
            }

            $hostname = $func($hostname, 0, INTL_IDNA_VARIANT_UTS46);

            $this->setCurrentPath($request->getBasePath().'/');
        }
    }

    public function setCurrentFolderId(int $current_folder_id): self
    {
        $this->current_folder_id = $current_folder_id;

        return $this;
    }

    public function getCurrentFolderId(): int
    {
        return $this->current_folder_id;
    }

    public function setCurrentNodeId(?int $current_node_id): self
    {
        $this->current_node_id = $current_node_id;

        return $this;
    }

    public function getCurrentNodeId(): ?int
    {
        return $this->current_node_id;
    }

    public function getCurrentPath(): string
    {
        return $this->current_path;
    }

    public function setCurrentPath(string $current_path): self
    {
        $this->current_path = $current_path;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getSite(bool $force = false): ?Site
    {
        if ($force and $this->site instanceof Site) {
            if ( ! $this->em->contains($this->site)) {
                $site = $this->em->find(Site::class, $this->site->getId());

                $this->site = $site;
            }
        }

        return $this->site;
    }

    public function getSiteId(): ?int
    {
        return $this->site ? $this->site->getId() : null;
    }

    public function getRenderedRegions(): array
    {
        return $this->rendered_regions;
    }

    /**
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

    public function setRenderedRegions(array $rendered_regions): self
    {
        $this->rendered_regions = $rendered_regions;

        return $this;
    }

    public function getSiteEm(): ObjectManager
    {
        return $this->siteEm;
    }
}
