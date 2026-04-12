<?php

namespace Database\Seeders;

use App\Models\FunnelTemplate;
use App\Models\FunnelTemplateStep;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FunnelTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = $this->resolveSuperAdminCreatorId();
        $catalog = array_merge($this->stepByStepPlaceholders(), $this->jsonTemplates());

        foreach ($catalog as $templateDefinition) {
            $template = FunnelTemplate::query()->updateOrCreate(
                ['slug' => $templateDefinition['slug']],
                [
                    'created_by' => $creatorId,
                    'name' => $templateDefinition['name'],
                    'description' => $templateDefinition['description'] ?? null,
                    'template_type' => $templateDefinition['template_type'] ?? 'single_page',
                    'template_tags' => $templateDefinition['template_tags'] ?? [],
                    'status' => $templateDefinition['status'] ?? 'draft',
                    'published_at' => ($templateDefinition['status'] ?? 'draft') === 'published' ? now() : null,
                ]
            );

            $this->syncTemplateSteps($template, $templateDefinition['steps'] ?? []);
        }
    }

    private function resolveSuperAdminCreatorId(): ?int
    {
        $superAdminRoleId = Role::query()->where('slug', 'super-admin')->value('id');

        if ($superAdminRoleId) {
            $superAdminId = User::query()
                ->whereHas('roles', fn ($query) => $query->where('roles.id', $superAdminRoleId))
                ->value('id');

            if ($superAdminId) {
                return (int) $superAdminId;
            }
        }

        $fallbackId = User::query()->value('id');

        return $fallbackId ? (int) $fallbackId : null;
    }

    private function jsonTemplates(): array
    {
        $templatesDir = database_path('seeders/templates');
        if (! is_dir($templatesDir)) {
            return [];
        }

        $templateFiles = glob($templatesDir . '/*.json') ?: [];

        return collect($templateFiles)
            ->map(function (string $path) {
                try {
                    $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
                    if (! is_array($decoded)) {
                        return null;
                    }

                    return $decoded;
                } catch (\Throwable) {
                    return null;
                }
            })
            ->filter(function ($template) {
                return is_array($template)
                    && isset($template['slug'], $template['name'], $template['steps'])
                    && (($template['template_type'] ?? null) === 'single_page');
            })
            ->map(function (array $template) {
                $template['status'] = 'published';
                $template['template_tags'] = is_array($template['template_tags'] ?? null) ? $template['template_tags'] : [];
                $template['steps'] = is_array($template['steps'] ?? null) ? $template['steps'] : [];

                return $template;
            })
            ->values()
            ->all();
    }

    private function stepByStepPlaceholders(): array
    {
        return [
            [
                'name' => 'Step-by-Step Digital Services (Placeholder)',
                'slug' => 'step-by-step-digital-services-placeholder',
                'description' => 'Placeholder only. Step-by-step catalog is visible but not active yet.',
                'template_type' => 'service',
                'template_tags' => ['Step-by-Step', 'Placeholder'],
                'status' => 'draft',
                'steps' => [],
            ],
            [
                'name' => 'Step-by-Step Physical Products (Placeholder)',
                'slug' => 'step-by-step-physical-products-placeholder',
                'description' => 'Placeholder only. Step-by-step catalog is visible but not active yet.',
                'template_type' => 'physical_product',
                'template_tags' => ['Step-by-Step', 'Placeholder'],
                'status' => 'draft',
                'steps' => [],
            ],
        ];
    }

    private function syncTemplateSteps(FunnelTemplate $template, array $steps): void
    {
        $keptSlugs = [];

        foreach (array_values($steps) as $index => $stepDefinition) {
            $slug = Str::slug((string) ($stepDefinition['slug'] ?? ('step-' . ($index + 1))));
            $slug = $slug !== '' ? $slug : ('step-' . ($index + 1));
            $keptSlugs[] = $slug;

            FunnelTemplateStep::query()->updateOrCreate(
                [
                    'funnel_template_id' => $template->id,
                    'slug' => $slug,
                ],
                [
                    'title' => (string) ($stepDefinition['title'] ?? ('Step ' . ($index + 1))),
                    'subtitle' => $stepDefinition['subtitle'] ?? null,
                    'type' => (string) ($stepDefinition['type'] ?? 'landing'),
                    'content' => $stepDefinition['content'] ?? null,
                    'cta_label' => $stepDefinition['cta_label'] ?? null,
                    'price' => $stepDefinition['price'] ?? null,
                    'position' => $index + 1,
                    'is_active' => array_key_exists('is_active', $stepDefinition) ? (bool) $stepDefinition['is_active'] : true,
                    'template' => (string) ($stepDefinition['template'] ?? 'simple'),
                    'template_data' => is_array($stepDefinition['template_data'] ?? null) ? $stepDefinition['template_data'] : [],
                    'step_tags' => is_array($stepDefinition['step_tags'] ?? null) ? $stepDefinition['step_tags'] : [],
                    'layout_json' => is_array($stepDefinition['layout_json'] ?? null)
                        ? $stepDefinition['layout_json']
                        : ['root' => [], 'sections' => []],
                ]
            );
        }

        $query = FunnelTemplateStep::query()->where('funnel_template_id', $template->id);
        if ($keptSlugs === []) {
            $query->delete();
            return;
        }

        $query->whereNotIn('slug', $keptSlugs)->delete();

        foreach ($keptSlugs as $index => $slug) {
            FunnelTemplateStep::query()
                ->where('funnel_template_id', $template->id)
                ->where('slug', $slug)
                ->update(['position' => $index + 1]);
        }
    }
}