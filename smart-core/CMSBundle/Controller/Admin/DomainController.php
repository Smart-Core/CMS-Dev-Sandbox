<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\Form\Type\DomainFormType;
use SmartCore\CMSBundle\Manager\CmsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/domain')]
class DomainController extends AbstractController
{
    #[Route('/', name: 'cms_admin.domains')]
    public function index(CmsManager $cmsManager): Response
    {
        return $this->render('@CMS/admin/domain/index.html.twig', [
            'domains' => $cmsManager->getDomains(),
        ]);
    }

    #[Route('/domain_create/', name: 'cms_admin.domain_create')]
    public function create(CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $form = $this->createForm(DomainFormType::class, new Domain());
        $form->remove('delete');
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Домен добавлен.');

                return $this->redirectToRoute('cms_admin.domains');
            }
        }

        return $this->render('@CMS/admin/domain/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/', name: 'cms_admin.domain_edit')]
    #[ParamConverter('domain', options: ['entity_manager' => 'cms'])]
    public function edit(Domain $domain, CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $form = $this->createForm(DomainFormType::class, $domain);
        $form->remove('create');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('delete')->isClicked() and $form->isValid()) {
                $em->remove($form->getData());
                $em->flush();

                $this->addFlash('success', 'Domain удалён.');

                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Domain обновлён.');

                return $this->redirectToRoute('cms_admin.domains');
            }
        }

        return $this->render('@CMS/admin/domain/edit.html.twig', [
            'domain' => $domain,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/domain_create_alias/{id<\d+>}/', name: 'cms_admin.domain_create_alias')]
    #[ParamConverter('domain', options: ['entity_manager' => 'cms'])]
    public function createAlias(Domain $domain, CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $alias = new Domain();
        $alias->setParent($domain);

        $form = $this->createForm(DomainFormType::class, $alias);
        $form->remove('delete');
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Domain alias добавлен.');

                return $this->redirectToRoute('cms_admin.domains');
            }
        }

        return $this->render('@CMS/admin/domain/create_alias.html.twig', [
            'domain' => $domain,
            'form'   => $form->createView(),
        ]);
    }
}
