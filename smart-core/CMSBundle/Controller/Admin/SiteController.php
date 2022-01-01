<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\EntityCms\SiteLanguage;
use SmartCore\CMSBundle\Form\Type\SiteFormType;
use SmartCore\CMSBundle\Form\Type\SiteLanguageFormType;
use SmartCore\CMSBundle\Form\Type\SiteMlModeOffFormType;
use SmartCore\CMSBundle\Manager\CmsManager;
use SmartCore\CMSBundle\Manager\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/site')]
class SiteController extends AbstractController
{
    #[Route('/', name: 'cms_admin.site')]
    public function index(SecurityManager $securityManager, CmsManager $cmsManager): Response
    {
        return $this->render('@CMS/admin/site/index.html.twig', [
            'sites' => $cmsManager->getSites(),
        ]);
    }

    #[Route('/create/', name: 'cms_admin.site_create')]
    public function create(Request $request, CmsManager $cmsManager): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $form = $this->createForm(SiteFormType::class, new Site());
        $form->remove('update');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form->get('create')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Сайт добавлен.');

                return $this->redirectToRoute('cms_admin.site');
            }
        }

        return $this->render('@CMS/admin/site/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}/', name: 'cms_admin.site_edit')]
    #[ParamConverter('site', options: ['entity_manager' => 'cms'])]
    public function edit(Site $site, CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        $form = $this->createForm(SiteFormType::class, $site);
        $form->remove('create');

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->has('cancel') and $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Site updated successfully');

                return $this->redirectToRoute('cms_admin.site');
            }
        }

        return $this->render('@CMS/admin/site/edit.html.twig', [
            'form' => $form->createView(),
            'site' => $site,
        ]);
    }

    #[Route('/{id<\d+>}/i18n/', name: 'cms_admin.site_i18n')]
    #[ParamConverter('site', options: ['entity_manager' => 'cms'])]
    public function i18n(Site $site, CmsManager $cmsManager, Request $request): Response|RedirectResponse
    {
        $em = $cmsManager->getEm();

        // ML OFF
        $form = $this->createForm(SiteMlModeOffFormType::class, $site);

        // ML DOMAIN
        $form2 = $this->createForm(SiteLanguageFormType::class, new SiteLanguage($site));
        $form2->remove('delete');
        $form2->remove('update');

        // ML PATH and COOKIE
        $form3 = $this->createForm(SiteLanguageFormType::class, new SiteLanguage($site));
        $form3->remove('delete');
        $form3->remove('update');

        if ($request->query->has('del')) {
            $siteLanguage = $em->find(SiteLanguage::class, (int) $request->query->get('del'));

            if ($siteLanguage) {
                if ($site->getMultilanguageMode() === Site::MULTILANGUAGE_MODE_PATH
                    and $site->getDefaultLanguage() == $siteLanguage->getLanguage()
                ) {
                    $site->setDefaultLanguage(null);

                    foreach ($site->getLanguages() as $language) {
                        if ($language->getLanguage() !== $siteLanguage->getLanguage()) {
                            $site->setDefaultLanguage($language->getLanguage());
                            break;
                        }
                    }
                }

                $em->remove($siteLanguage);
                $em->flush();

                $this->addFlash('success', 'Site i18n deleted successfully');
            }

            return $this->redirectToRoute('cms_admin.site_i18n', ['id' => $site->getId()]);
        }

        if ($request->query->has('default')) {
            $siteLanguage = $em->find(SiteLanguage::class, (int) $request->query->get('default'));

            if ($siteLanguage) {
                $site->setDefaultLanguage($siteLanguage->getLanguage());
                $em->flush();

                $this->addFlash('success', 'Change default site languale successfully');
            }

            return $this->redirectToRoute('cms_admin.site_i18n', ['id' => $site->getId()]);
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $form2->handleRequest($request);
            $form3->handleRequest($request);

            if ($form->has('cancel') and $form->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form2->has('cancel') and $form2->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form3->has('cancel') and $form3->get('cancel')->isClicked()) {
                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form->get('update')->isClicked() and $form->isValid()) {
                $em->persist($form->getData());
                $em->flush();

                $this->addFlash('success', 'Site i18n updated successfully');

                return $this->redirectToRoute('cms_admin.site');
            }

            if ($form2->get('create')->isClicked() and $form2->isValid()) {
                if ($site->getLanguages()->count() === 0) {
                    $site->setDefaultLanguage($form2->getData()->getLanguage());
                }

                $em->persist($form2->getData());
                $em->flush();

                $this->addFlash('success', 'Язык сайта добавлен.');

                return $this->redirectToRoute('cms_admin.site_i18n', ['id' => $site->getId()]);
            }

        }

        return $this->render('@CMS/admin/site/i18n.html.twig', [
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'form3' => $form3->createView(),
            'site' => $site,
        ]);
    }
}
