<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Module\Menu\Entity\Menu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use SmartCore\Module\Menu\Entity\MenuItem;
use SmartCore\Module\Menu\Form\Type\MenuFormType;
use SmartCore\Module\Menu\Form\Type\ItemFormType;

class AdminController extends AbstractController
{
    public function indexAction(Request $request)
    {
        $form = $this->createForm(MenuFormType::class);

        if ($request->isMethod('POST') and $request->request->has('create')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $menu = $form->getData();
                $menu->setUser($this->getUser());
                $this->persist($menu, true);

                $this->addFlash('success', 'Меню создано.');

                return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $menu->getId()]);
            }
        }

        return $this->render('@MenuModule/Admin/index.html.twig', [
            'menus' => $this->get('doctrine.orm.default_entity_manager')->getRepository(Menu::class)->findAll(),
            'form'  => $form->createView(),
        ]);
    }

    /**
     * Редактирование пункта меню.
     *
     * @param Request $request
     * @param int $item_id
     *
     * @return RedirectResponse|Response
     */
    public function itemAction(Request $request, $item_id)
    {
        /** @var MenuItem $item */
        $item = $this->get('doctrine.orm.default_entity_manager')->find(MenuItem::class, $item_id);

        $form = $this->createForm(ItemFormType::class, $item);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->persist($form->getData(), true);

                    $this->getCacheService()->deleteTag('smart_module.menu');
                    $this->addFlash('success', 'Пункт меню обновлён.');

                    return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $item->getMenu()->getId()]);
                }
            } elseif ($request->request->has('delete')) {
                // @todo безопасное удаление, в частности отключение из нод и удаление всех связаных пунктов меню.
                $this->remove($form->getData(), true);

                $this->getCacheService()->deleteTag('smart_module.menu');
                $this->addFlash('success', 'Пункт меню удалён.');

                return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $item->getMenu()->getId()]);
            }
        }

        return $this->render('@MenuModule/Admin/item.html.twig', [
            'item' => $item,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Редактирование свойств группы меню.
     *
     * @param Request $request
     * @param int $menu_id
     *
     * @return RedirectResponse|Response
     */
    public function menuEditAction(Request $request, $menu_id)
    {
        $menu = $this->get('doctrine.orm.default_entity_manager')->find(Menu::class, $menu_id);

        if (empty($menu)) {
            return $this->redirectToRoute('smart_module.menu.admin');
        }

        $form = $this->createForm(MenuFormType::class, $menu);

        if ($request->isMethod('POST')) {
            if ($request->request->has('update')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->persist($form->getData(), true);

                    $this->getCacheService()->deleteTag('smart_module.menu');
                    $this->addFlash('success', 'Группа меню обновлена.');

                    return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $menu_id]);
                }
            } elseif ($request->request->has('delete')) {
                // @todo безопасное удаление, в частности отключение из нод и удаление всех связаных пунктов меню.
                $this->remove($form->getData(), true);

                $this->getCacheService()->deleteTag('smart_module.menu');
                $this->addFlash('success', 'Группа меню удалена.');

                return $this->redirectToRoute('smart_module.menu.admin');
            }
        }

        return $this->render('@MenuModule/Admin/menu_edit.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Редактирование группы меню.
     *
     * @param Request $request
     * @param int $menu_id
     *
     * @return RedirectResponse|Response
     */
    public function menuAction(Request $request, $menu_id)
    {
        $menu = $this->get('doctrine.orm.default_entity_manager')->find(Menu::class, $menu_id);

        if (empty($menu)) {
            return $this->redirectToRoute('smart_module.menu.admin');
        }

        $form = $this->createForm(ItemFormType::class, new MenuItem($menu));

        if ($request->isMethod('POST')) {
            if ($request->request->has('create_item')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    /** @var MenuItem $item */
                    $item = $form->getData();
                    $item
                        ->setUser($this->getUser())
                        ->setMenu($menu)
                    ;

                    $this->persist($item, true);

                    $this->addFlash('success', 'Пункт меню создан.');

                    return $this->redirectToRoute('smart_module.menu.admin_menu', ['menu_id' => $menu_id]);
                }
            }
        }

        return $this->render('@MenuModule/Admin/menu.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
        ]);
    }
}
