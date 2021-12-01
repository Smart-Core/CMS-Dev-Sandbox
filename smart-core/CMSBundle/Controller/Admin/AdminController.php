<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use SmartCore\CMSBundle\Manager\SecurityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends AbstractController
{
    #[Route('/', name: 'cms_admin.index')]
    public function index(SecurityManager $securityManager, AuthenticationUtils $helper): Response
    {
        if (!$securityManager->isGranted('ROLE_ADMIN')) {
            return $this->render('@CMS/admin/login.html.twig', [
                // last username entered by the user (if any)
                'last_username' => $helper->getLastUsername(),
                // last authentication error (if any)
                'error' => $helper->getLastAuthenticationError(),
            ]);
        }

        return $this->render('@CMS/admin/index.html.twig');
    }
}
