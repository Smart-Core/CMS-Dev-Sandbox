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

#[Route('/language')]
class LanguageController extends AbstractController
{
    #[Route('/', name: 'cms_admin.language')]
    public function index(CmsManager $cmsManager): Response
    {
        return $this->render('@CMS/admin/language/index.html.twig', [
            'languages' => [],
        ]);
    }

    #[Route('/create/', name: 'cms_admin.language_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        return $this->render('@CMS/admin/language/create.html.twig', [
            //'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/', name: 'cms_admin.language_edit')]
    public function edit(Request $request, Site $site): Response
    {
        return $this->render('@CMS/admin/language/edit.html.twig', [
            //'form'    => $form->createView(),
        ]);
    }
}
