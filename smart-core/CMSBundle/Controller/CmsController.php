<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Twig\Error\LoaderError;

class CmsController extends AbstractController
{
    /**
     * @Route("/{slug<.+>}", name="cms_main", methods={"GET"})
     */
    public function main(
        KernelInterface $kernel,
        Request $request,
        string $slug = '',
        array $options = null
    ): Response {

        return $this->render('@CMS/welcome.html.twig');
    }
}
