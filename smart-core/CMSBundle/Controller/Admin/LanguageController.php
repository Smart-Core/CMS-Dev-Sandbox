<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\Form\Type\LanguageFormType;
use SmartCore\CMSBundle\Manager\CmsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            'languages' => $cmsManager->getEm()->getRepository(Language::class)->findBy([], ['position' => 'ASC']),
        ]);
    }

    #[Route('/create/', name: 'cms_admin.language_create')]
    public function create(CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $language = new Language();

        $form = $this->createForm(LanguageFormType::class, $language);
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->has('cancel') and $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.language');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Language created successfully');

                return $this->redirectToRoute('cms_admin.language');
            }
        }

        return $this->render('@CMS/admin/language/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/', name: 'cms_admin.language_edit')]
    #[ParamConverter('language', options: ['entity_manager' => 'cms'])]
    public function edit(Language $language, CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $form = $this->createForm(LanguageFormType::class, $language);
        $form->remove('create');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->has('cancel') and $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.language');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Language updated successfully');

                return $this->redirectToRoute('cms_admin.language');
            }
        }

        return $this->render('@CMS/admin/language/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
