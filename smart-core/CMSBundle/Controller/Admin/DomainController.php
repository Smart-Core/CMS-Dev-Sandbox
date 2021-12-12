<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\Manager\CmsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    public function create(Request $request): Response
    {
        $form = $this->createForm(DomainFormType::class, new Domain());
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn-primary']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                /** @var Domain $language */
                $domain = $form->getData();
                $domain->setUser($this->getUser());

                $this->persist($domain, true);

                $this->addFlash('success', 'Domain добавлен.');

                return $this->redirectToRoute('cms_admin.domains');
            }
        }

        return $this->render('@CMS/admin/domain/create.html.twig', [
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/domain/{id<\d+>}/', name: 'cms_admin.domain_edit')]
    public function edit(Request $request, Domain $domain): Response
    {
        $form = $this->createForm(DomainFormType::class, $domain);
        $form->add('update', SubmitType::class, ['attr' => ['class' => 'btn-primary']]);
        $form->add('delete', SubmitType::class, ['attr' => ['class' => 'btn-danger', 'onclick' => "return confirm('Вы уверены, что хотите удалить домен?')", 'formnovalidate' => 'formnovalidate']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('delete')->isClicked() and $form->isValid()) {
                $this->remove($form->getData(), true);
                $this->addFlash('success', 'Domain удалён.');

                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $this->persist($form->getData(), true);
                $this->addFlash('success', 'Domain обновлён.');

                return $this->redirectToRoute('cms_admin.domains');
            }
        }

        return $this->render('@CMS/admin/domain/edit.html.twig', [
            'form'    => $form->createView(),
        ]);
    }

    #[Route('/domain_create_alias/{id<\d+>}/', name: 'cms_admin.domain_create_alias')]
    public function createAlias(Request $request, Domain $domain): Response
    {
        $alias = new Domain();
        $alias->setParent($domain);

        $form = $this->createForm(DomainFormType::class, $alias);
        $form->add('create', SubmitType::class, ['attr' => ['class' => 'btn-primary']]);
        $form->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-default', 'formnovalidate' => 'formnovalidate']]);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.domains');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                /** @var Domain $language */
                $domain = $form->getData();
                $domain->setUser($this->getUser());

                $this->persist($domain, true);

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
