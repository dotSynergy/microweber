<?php

namespace Modules\Settings\Filament\Pages;

use Arcanedev\Html\Elements\P;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Page;
use MicroweberPackages\Filament\Facades\FilamentRegistry;
use MicroweberPackages\Filament\Support\FilamentHelpers;
use Modules\Payment\Filament\Admin\Resources\PaymentProviderResource;


class Settings extends Page
{
    protected static ?string $navigationIcon = 'mw-settings';

    protected static string $view = 'modules.settings::filament.admin.pages.settings-main';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 97;


    public function getBreadcrumb(): string
    {
        return '';
    }

    public function getTitle(): string
    {
        return '';
    }


    public function getViewData(): array
    {
        $settingsPages = [];
        $settingsPages[] = new AdminGeneralPage();
        $settingsPages[] = new AdminEmailPage();
        $settingsPages[] = new AdminTemplatePage();
        $settingsPages[] = new AdminSeoPage();
        $settingsPages[] = new AdminAdvancedPage();
        $settingsPages[] = new AdminFilesPage();
        $settingsPages[] = new AdminLoginRegisterPage();
        $settingsPages[] = new AdminLanguagePage();
        $settingsPages[] = new AdminPrivacyPolicyPage();
        $settingsPages[] = new AdminWebManifestPage();
        //$settingsPages[] = new AdminExperimentalPage();
        $settingsPages[] = new AdminMaintenanceModePage();
        $settingsPages[] = new AdminUiColorsPage();
        $settingsPages[] = new AdminPoweredByPage();
        $settingsPages[] = new AdminRobotsPage();
        $settingsPages[] = new AdminTrustProxiesPage();
        $settingsPages[] = new AdminCustomTagsPage();

        $settingsPages[] = new AdminShopGeneralPage();
        $settingsPages[] = new AdminShopShippingPage();
        $settingsPages[] = new AdminShopCouponsPage();
        $settingsPages[] = new AdminShopOffersPage();
      //  $settingsPages[] = new AdminShopPaymentsPage();
        $settingsPages[] = new AdminShopTaxesPage();
        $settingsPages[] = new AdminShopInvoicesPage();
        $settingsPages[] = new AdminShopAutoRespondEmailPage();
        $settingsPages[] = new AdminShopOtherPage();
   //     $settingsPages[] = new AdminShopOtherPage();

        $registeredSettingsPages = FilamentRegistry::getPages(self::class, Filament::getCurrentPanel()->getId());
        $registeredSettingsResources = FilamentRegistry::getResources(self::class, Filament::getCurrentPanel()->getId());


        if (!empty($registeredSettingsPages)) {
            foreach ($registeredSettingsPages as $registeredSettingsPage) {
                $settingsPages[] = new $registeredSettingsPage;
            }
        }

        if (!empty($registeredSettingsResources)) {
            foreach ($registeredSettingsResources as $registeredSettingsResource) {

                $settingsPages[] = new $registeredSettingsResource;
            }
        }

        $settingsGroups = [];
        $panel = Filament::getCurrentPanel()->getId();
        //  $panelNavigationGroups = Filament::getCurrentPanel()->getNavigationGroups();
        $panelNavigationItems = Filament::getCurrentPanel()->getNavigation();

 //dd($panelNavigationItems);
        if ($panelNavigationItems) {
            foreach ($panelNavigationItems as $navGroup) {


                //if not end with settings skip
                if (method_exists($navGroup, 'getLabel') && !str_ends_with($navGroup->getLabel(), 'Settings')) {
                    continue;
                }
                if (method_exists($navGroup, 'getLabel') && $navGroup->getLabel() ==  'Settings') {
                    continue;
                }

                $settingsGroupsNavGroup = $this->buildNavFromPanelNavGroup($navGroup);

                if (!empty($settingsGroupsNavGroup)) {
                    foreach ($settingsGroupsNavGroup as $itemsNavGroup) {
                        foreach ($itemsNavGroup as $itemsNavGroupItem) {

                            $settingsGroups[$navGroup->getLabel()][] = $itemsNavGroupItem;
                        }
                    }
                    //dd($settingsGroupsNavGroup);
                    //  $settingsGroups = array_merge($settingsGroups, $settingsGroupsNavGroup);
                }

            }
        }
        $positionFallback = 0;
        foreach ($settingsPages as $settingsPage) {

            $instance = new $settingsPage;
            $defaultGroup = 'Website Settings';

            $group = method_exists($settingsPage, 'getNavigationGroup')
                ? $settingsPage::getNavigationGroup()
                : $defaultGroup;

            $slug = method_exists($instance, 'getSlug') ? $instance->getSlug() : '';

            if (isset($settingsGroups[$group]) && array_search($slug, array_column($settingsGroups[$group], 'slug')) !== false) {
                continue;
            }

            $title = '';
            if (method_exists($instance, 'getTitle')) {
                $title = $instance->getTitle();
            } elseif (method_exists($instance, 'getNavigationLabel')) {
                $title = $instance->getNavigationLabel();
            }

            $description = '';
            if (method_exists($instance, 'getDescription')) {
                $description = $instance->getDescription();
            }

            $heading = '';
            if (method_exists($instance, 'getHeading')) {
                $heading = $instance->getHeading();
            }

            $icon = '';
            if (method_exists($instance, 'getNavigationIcon')) {
                $icon = $instance->getNavigationIcon();
            }

            $url = '';
            if (method_exists($settingsPage, 'getNavigationUrl')) {
                $url = $settingsPage::getNavigationUrl();
            }

            $position = $positionFallback;
            if (method_exists($instance, 'getNavigationSort')) {

                $position = $settingsPage::getNavigationSort() ?? $positionFallback;
            }


            $settingsGroups[$group][] = [
                'title' => $title,
                'description' => $description,
                'heading' => $heading,
                'slug' => $slug,
                'icon' => $icon,
                'url' => $url,
                'position' => $position,
            ];


            $positionFallback++;

        }


        //sort $settingsGroups items postion iside the groups

        foreach ($settingsGroups as $group => $items) {
            usort($items, function ($a, $b) {
                if (isset($a['position']) && isset($b['position']) && $a['position'] === $b['position']) {
                    return 0;

                } else if (!isset($a['position']) && !isset($b['position'])) {
                    return 0;
                } elseif (!isset($a['position'])) {
                    return 1;
                } elseif (!isset($b['position'])) {
                    return -1;
                }


                return $a['position'] <=> $b['position'];
            });
            $settingsGroups[$group] = $items;
        }


        return [
            'settingsGroups' => $settingsGroups,
        ];
    }


    private function buildNavFromPanelNavGroup(NavigationGroup $navGroup)
    {
        $settingsGroups = [];

        $groupLabel = '';
        if (method_exists($navGroup, 'getLabel')) {
            $groupLabel = $navGroup->getLabel();
        } elseif (method_exists($navGroup, 'getTitle')) {
            $groupLabel = $navGroup->getTitle();
        }

        if (method_exists($navGroup, 'getItems')) {
            foreach ($navGroup->getItems() as $item) {

                $itemData = [
                    'title' => '',
                    'description' => '',
                    'heading' => '',
                    'slug' => '',
                    'icon' => '',
                    'url' => ''
                ];

                try {
                    if (method_exists($item, 'getLabel')) {
                        $itemData['title'] = $item->getLabel();
                        $itemData['heading'] = $item->getLabel();
                    }
                } catch (\Exception $e) {
                }

                try {
                    if (method_exists($item, 'getDescription')) {
                        $itemData['description'] = $item->getDescription();
                    }
                } catch (\Exception $e) {
                }

                if (!isset($itemData['description']) or $itemData['description'] == '') {
                    try {
                        if (method_exists($item, 'getNavgationLabel')) {
                            $itemData['description'] = $item->getNavgationLabel();
                        }
                    } catch (\Exception $e) {
                    }
                }


                try {
                    if (method_exists($item, 'getSlug')) {
                        $itemData['slug'] = $item->getSlug();
                    }
                } catch (\Exception $e) {
                }

                if (!isset($itemData['icon']) or $itemData['icon'] == '') {

                    $itemData['icon'] = FilamentHelpers::getNavigationItemIcon($item);
                }

                try {
                    if (method_exists($item, 'getUrl')) {
                        $itemData['url'] = $item->getUrl();
                    }
                } catch (\Exception $e) {
                }


                if (!isset($itemData['description']) or $itemData['description'] == '') {
                    // a reflection class for the item to get the description
                    $itemData['description'] = FilamentHelpers::getNavigationItemDescription($item);

                }


                $settingsGroups[$groupLabel][] = $itemData;


                if (method_exists($item, 'getChildItems')) {
                    try {
                        $childItems = $item->getChildItems();
                        if (!empty($childItems)) {
                            foreach ($childItems as $childItem) {
                                $childItemData = [
                                    'title' => '',
                                    'description' => '',
                                    'heading' => '',
                                    'slug' => '',
                                    'icon' => 'mw-general',
                                    'url' => ''
                                ];

                                try {
                                    if (method_exists($childItem, 'getLabel')) {
                                        $childItemData['title'] = $childItem->getLabel();
                                        $childItemData['heading'] = $childItem->getLabel();
                                    }
                                } catch (\Exception $e) {
                                }

                                try {
                                    if (method_exists($childItem, 'getDescription')) {
                                        $childItemData['description'] = $childItem->getDescription();
                                    }
                                } catch (\Exception $e) {
                                }

                                try {
                                    if (method_exists($childItem, 'getSlug')) {
                                        $childItemData['slug'] = $childItem->getSlug();
                                    }
                                } catch (\Exception $e) {
                                }

                                try {
                                    if (method_exists($childItem, 'getIcon')) {
                                        $childItemData['icon'] = $childItem->getIcon();
                                    }
                                } catch (\Exception $e) {
                                }

                                try {
                                    if (method_exists($childItem, 'getUrl')) {
                                        $childItemData['url'] = $childItem->getUrl();
                                    }
                                } catch (\Exception $e) {
                                }

                                if (!isset($childItemData['description']) or $childItemData['description'] == '') {
                                    // a reflection class for the item to get the description
                                    $childItemData['description'] = FilamentHelpers::getNavigationItemDescription($childItem);

                                }


                                $settingsGroups[$groupLabel][] = $childItemData;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        return $settingsGroups;
    }
}
