<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use SmartCore\CMSBundle\Manager\CmsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/theme')]
class ThemeController extends AbstractController
{
    #[Route('/', name: 'cms_admin.theme')]
    public function index(CmsManager $cmsManager): Response
    {
        return $this->render('@CMS/admin/theme/index.html.twig', [
            'themes' => [],
        ]);
    }
}
