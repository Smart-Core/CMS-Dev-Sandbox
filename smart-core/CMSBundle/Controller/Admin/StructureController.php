<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use SmartCore\CMSBundle\Manager\ContextManager;
use SmartCore\CMSBundle\Site\Form\Type\FolderFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/folder/create/', name: 'cms_admin.structure_folder_create')]
    public function folderCreate(Request $request, ContextManager $contextManager): Response|RedirectResponse
    {
        $em = $contextManager->getSiteEm();

        $form = $this->createForm(FolderFormType::class);
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->has('cancel') and $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.structure');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Folder created successfully');

                return $this->redirectToRoute('cms_admin.structure');
            }
        }

        return $this->render('@CMS/admin/structure/folder_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
