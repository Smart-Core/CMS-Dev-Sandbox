<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\Manager\CmsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/module')]
class ModuleController extends AbstractController
{
    #[Route('/', name: 'cms_admin.module')]
    public function index(CmsManager $cmsManager): Response
    {
        return $this->render('@CMS/admin/module/index.html.twig', [
            'modules' => [],
        ]);
    }
}
