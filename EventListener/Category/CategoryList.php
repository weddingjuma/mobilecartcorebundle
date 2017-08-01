<?php

namespace MobileCart\CoreBundle\EventListener\Category;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CategoryList
 * @package MobileCart\CoreBundle\EventListener\Category
 */
class CategoryList
{

    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCategoryList(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $response = '';

        $returnData['columns'] = [
            [
                'key' => 'id',
                'label' => 'ID',
                'sort' => 1,
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'sort' => 1,
            ],
        ];

        if (isset($returnData['search'])) {
            $search = $returnData['search'];
            $sortBy = $search->getSortBy();
            $sortDir = $search->getSortDir();
            if ($sortBy) {
                foreach($returnData['columns'] as $k => $colData) {
                    if ($colData['key'] == $sortBy) {
                        $returnData['columns'][$k]['isActive'] = 1;
                        $returnData['columns'][$k]['direction'] = $sortDir;
                        break;
                    }
                }
            }
        }

        switch($event->getSection()) {
            case ($format == 'json'):
            case CoreEvent::SECTION_API:

                $response = new JsonResponse($returnData);

                break;
            case CoreEvent::SECTION_BACKEND:

                $returnData['mass_actions'] = [
                    [
                        'label'         => 'Delete Categories',
                        'input_label'   => 'Confirm Mass-Delete ?',
                        'input'         => 'mass_delete',
                        'input_type'    => 'select',
                        'input_options' => [
                            ['value' => 0, 'label' => 'No'],
                            ['value' => 1, 'label' => 'Yes'],
                        ],
                        'url' => $this->router->generate('cart_admin_category_mass_delete'),
                        'external' => 0,
                    ],
                ];

                $template = $event->getCustomTemplate()
                    ? $event->getCustomTemplate()
                    : 'Category:index.html.twig';

                $response = $this->getThemeService()
                    ->render('admin', $template, $returnData);

                break;
            case CoreEvent::SECTION_FRONTEND:

                $template = $event->getCustomTemplate()
                    ? $event->getCustomTemplate()
                    : 'Category:index.html.twig';

                $response = $this->getThemeService()
                    ->render('frontend', $template, $returnData);

                break;
            default:

                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
