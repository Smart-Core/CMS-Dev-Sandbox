<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/structure')]
class StructureController extends AbstractController
{
    #[Route('/', name: 'cms_admin.structure')]
    public function index(): Response
    {
        return $this->render('@CMS/admin/structure/index.html.twig', [
            //'sites' => $cmsManager->getSites(),
        ]);
    }
}
