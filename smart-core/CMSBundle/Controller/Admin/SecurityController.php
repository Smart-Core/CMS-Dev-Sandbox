<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in config/packages/security.yaml
     */
    #[Route('/logout', name: 'cms_admin.logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
