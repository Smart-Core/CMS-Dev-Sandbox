<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\CMSBundle\Manager\SecurityManager;
use SmartCore\CMSBundle\Site\Manager\SiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/site')]
class SiteController extends AbstractController
{
    #[Route('/', name: 'cms_admin.site')]
    public function index(SecurityManager $securityManager, SiteManager $siteManager, CmsManager $cmsManager): Response
    {
        return $this->render('@CMS/admin/site/index.html.twig', [
            'sites' => $cmsManager->getSites(),
        ]);
    }

    #[Route('/create/', name: 'cms_admin.site_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $language = $em->getRepository(Language::class)->findOneBy([], ['position' => 'ASC']);

        $site = new Site();
        $site
            ->setLanguage($language)
            ->setUser($this->getUser())
        ;

        $form = $this->createForm(SiteFormType::class, $site);
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn-primary']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $this->persist($form->getData(), true);

                $this->addFlash('success', 'Site добавлен.');

                return $this->redirectToRoute('cms_admin.site');
            }
        }

        return $this->render('@CMS/admin/site/create.html.twig', [
            //'form'    => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/', name: 'cms_admin.site_edit')]
    #[ParamConverter('site', options: ['entity_manager' => 'cms'])]
    public function edit(Request $request, Site $site): Response
    {
        return $this->render('@CMS/admin/site/edit.html.twig', [
            //'form'    => $form->createView(),
        ]);
    }
}
