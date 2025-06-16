<?php

namespace Modules\Slider\Microweber;

use MicroweberPackages\Microweber\Abstract\BaseModule;
use Modules\Slider\Filament\SliderModuleSettings;
use Modules\Slider\Models\Slider;

class SliderModule extends BaseModule
{
    public static string $name = 'Slider';
    public static string $module = 'slider';
    public static string $icon = 'modules.slider-icon';
    public static string $categories = 'media';
    public static int $position = 58;
    public static string $settingsComponent = SliderModuleSettings::class;
    public static string $templatesNamespace = 'modules.slider::templates';

    public function render()
    {
        $viewData = $this->getViewData();
        $viewData['slides'] = $this->getSlides();

        $template = $viewData['template'] ?? 'default';
        if (!view()->exists(static::$templatesNamespace . '.' . $template)) {
            $template = 'default';
        }

        return view(static::$templatesNamespace . '.' . $template, $viewData);
    }

    protected function getSlides()
    {
        $relId = $this->getRelId();
        $relType = $this->getRelType();

        $slides = Slider::where('rel_type', $relType)
            ->where('rel_id', $relId)
            ->orderBy('position')
            ->get();

        $getSlidesCreatedDefault = $this->getOption('getSlidesCreatedDefault');

        if (!$getSlidesCreatedDefault && $slides->isEmpty()) {
            $this->saveOption('getSlidesCreatedDefault', '1');
            return collect($this->getDefaultSlides());
        }

        return $slides;
    }

    protected function getDefaultSlides(): array
    {
        $defaultContent = file_get_contents(module_path(self::$module) . '/resources/default-content/default_content.json');
        $defaultContent = json_decode($defaultContent, true);

        if (!isset($defaultContent['slides'])) {
            return [];
        }

        return array_map(function ($slide) {
            $slide['media'] = app()->url_manager->replace_site_url_back($slide['media']);

            $sliderModel = new Slider();
            $slide['rel_id'] = $this->getRelId();
            $slide['rel_type'] = $this->getRelType();
            $sliderModel->fill($slide);
            $sliderModel->save();

            return $sliderModel;
        }, $defaultContent['slides']);
    }

    protected function getRelId(): ?string
    {
        return $this->getOption('rel_id')
            ?? $this->params['rel_id']
            ?? $this->params['id']
            ?? null;
    }

    protected function getRelType(): string
    {
        return $this->getOption('rel_type')
            ?? $this->params['rel_type']
            ?? 'module';
    }
}
