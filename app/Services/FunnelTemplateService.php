<?php

namespace App\Services;

use App\Models\Funnel;
use App\Models\FunnelStep;
use App\Models\FunnelTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FunnelTemplateService
{
    public function createStarterTemplate(array $attributes, User $user): FunnelTemplate
    {
        return DB::transaction(function () use ($attributes, $user) {
            $templateType = FunnelTemplate::normalizeTemplateType($attributes['template_type'] ?? 'service');
            $template = FunnelTemplate::create([
                'created_by' => $user->id,
                'name' => $attributes['name'],
                'slug' => $this->generateUniqueTemplateSlug($attributes['name']),
                'description' => $attributes['description'] ?? null,
                'template_type' => $templateType,
                'template_tags' => $attributes['template_tags'] ?? [],
                'status' => 'draft',
            ]);

            $starterSteps = $this->starterTemplateStepsForType($templateType);

            foreach ($starterSteps as $index => $step) {
                $template->steps()->create([
                    'title' => $step['title'],
                    'slug' => $step['slug'],
                    'type' => $step['type'],
                    'content' => $step['content'],
                    'cta_label' => $step['cta_label'] ?? null,
                    'price' => $step['price'] ?? null,
                    'position' => $index + 1,
                    'is_active' => true,
                    'template' => 'simple',
                    'step_tags' => [],
                    'layout_json' => ['root' => [], 'sections' => []],
                ]);
            }

            return $template->fresh('steps');
        });
    }

    private function starterTemplateStepsForType(string $templateType): array
    {
        return match (FunnelTemplate::normalizeTemplateType($templateType)) {
            'single_page' => [
                ['title' => 'Single Page Funnel', 'slug' => 'single-page', 'type' => 'landing', 'content' => 'Build your complete one-page experience here (hero, offer, proof, checkout, and closing sections).', 'cta_label' => 'Get Started'],
            ],
            'digital_product' => [
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present the digital product offer and why it is worth buying.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Collect buyer info and complete payment for the digital product.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank the buyer and explain how access or delivery works.'],
            ],
            'physical_product' => [
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Show the product, its media, key benefits, pricing, and the reason to buy now.', 'cta_label' => 'Buy Now'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Collect customer, shipping, and payment details for the physical product order.', 'cta_label' => 'Place Order', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Confirm the order and tell the buyer when shipping or tracking details will be sent.'],
            ],
            'hybrid' => [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Introduce the offer and guide buyers to the main sales page.', 'cta_label' => 'Continue'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present the core offer details here.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Complete the order with the final buyer details and payment.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank the buyer and explain the next step clearly.'],
            ],
            default => [
                ['title' => 'Landing', 'slug' => 'landing', 'type' => 'landing', 'content' => 'Welcome to our template funnel.', 'cta_label' => 'Continue'],
                ['title' => 'Opt-in', 'slug' => 'opt-in', 'type' => 'opt_in', 'content' => 'Fill out the form to continue.', 'cta_label' => 'Submit'],
                ['title' => 'Sales', 'slug' => 'sales', 'type' => 'sales', 'content' => 'Present your offer details here.', 'cta_label' => 'Go to Checkout'],
                ['title' => 'Checkout', 'slug' => 'checkout', 'type' => 'checkout', 'content' => 'Complete your order.', 'cta_label' => 'Pay Now', 'price' => 1000],
                ['title' => 'Thank You', 'slug' => 'thank-you', 'type' => 'thank_you', 'content' => 'Thank you for your purchase.'],
            ],
        };
    }

    public function createFunnelFromTemplate(FunnelTemplate $template, User $user, array $overrides = []): Funnel
    {
        return DB::transaction(function () use ($template, $user, $overrides) {
            $template->loadMissing('steps');

            $name = trim((string) ($overrides['name'] ?? $template->name));
            $description = array_key_exists('description', $overrides)
                ? $overrides['description']
                : $template->description;

            $funnel = Funnel::create([
                'tenant_id' => $user->tenant_id,
                'created_by' => $user->id,
                'name' => $name !== '' ? $name : $template->name,
                'slug' => $this->generateUniqueFunnelSlug($name !== '' ? $name : $template->name, (int) $user->tenant_id),
                'description' => $description,
                'purpose' => $overrides['purpose'] ?? $template->template_type,
                'default_tags' => $this->templateDefaultTags($template),
                'status' => 'draft',
            ]);

            foreach ($template->steps->sortBy('position')->values() as $index => $step) {
                $funnel->steps()->create([
                    'title' => $step->title,
                    'subtitle' => $step->subtitle,
                    'slug' => $this->generateUniqueStepSlug($funnel, $step->slug ?: $step->title, $index + 1),
                    'type' => $step->type,
                    'content' => $step->content,
                    'cta_label' => $step->cta_label,
                    'price' => $step->price,
                    'position' => $index + 1,
                    'is_active' => (bool) $step->is_active,
                    'hero_image_url' => $step->hero_image_url,
                    'layout_style' => $step->layout_style,
                    'template' => $step->template ?: 'simple',
                    'template_data' => $step->template_data,
                    'step_tags' => $step->step_tags ?? [],
                    'background_color' => $step->background_color,
                    'button_color' => $step->button_color,
                    'layout_json' => is_array($step->layout_json) ? $step->layout_json : ['root' => [], 'sections' => []],
                ]);
            }

            return $funnel->fresh('steps');
        });
    }

    private function templateDefaultTags(FunnelTemplate $template): array
    {
        $tags = collect($template->template_tags ?? [])
            ->map(fn ($tag) => mb_strtolower(trim((string) $tag)))
            ->filter()
            ->values();

        if ($tags->contains('single-scroll') || $tags->contains('__single_scroll')) {
            return ['__single_scroll'];
        }

        return [];
    }

    public function importTemplateFromJson(array $payload, User $user, array $overrides = []): FunnelTemplate
    {
        return DB::transaction(function () use ($payload, $user, $overrides) {
            $steps = $this->extractImportedSteps($payload);
            if ($steps === []) {
                throw new \InvalidArgumentException('Imported template JSON must include at least one step.');
            }

            $name = trim((string) ($overrides['name'] ?? ($payload['name'] ?? 'Imported Template')));
            $description = array_key_exists('description', $overrides)
                ? $overrides['description']
                : ($payload['description'] ?? null);
            $templateTags = $overrides['template_tags'] ?? ($payload['template_tags'] ?? []);
            $publish = (bool) ($overrides['publish'] ?? false);

            $template = FunnelTemplate::create([
                'created_by' => $user->id,
                'name' => $name !== '' ? $name : 'Imported Template',
                'slug' => $this->generateUniqueTemplateSlug($name !== '' ? $name : 'Imported Template'),
                'description' => is_string($description) ? trim($description) : $description,
                'template_type' => $overrides['template_type'] ?? ($payload['template_type'] ?? 'service'),
                'template_tags' => is_array($templateTags) ? $templateTags : [],
                'status' => $publish ? 'published' : 'draft',
                'published_at' => $publish ? now() : null,
            ]);

            foreach (array_values($steps) as $index => $step) {
                $title = trim((string) ($step['title'] ?? $step['name'] ?? ('Step ' . ($index + 1))));
                $type = $this->normalizeImportedStepType($step['type'] ?? null);
                $template->steps()->create([
                    'title' => $title !== '' ? $title : ('Step ' . ($index + 1)),
                    'subtitle' => $this->nullableString($step['subtitle'] ?? null),
                    'slug' => $this->generateUniqueImportedTemplateStepSlug($template, (string) ($step['slug'] ?? $title), $index + 1),
                    'type' => $type,
                    'content' => $this->nullableString($step['content'] ?? null),
                    'cta_label' => $this->nullableString($step['cta_label'] ?? ($step['ctaLabel'] ?? null)),
                    'price' => $this->normalizeImportedPrice($step['price'] ?? null),
                    'position' => $index + 1,
                    'is_active' => array_key_exists('is_active', $step) ? (bool) $step['is_active'] : true,
                    'hero_image_url' => $this->nullableString($step['hero_image_url'] ?? null),
                    'layout_style' => $this->normalizeImportedLayoutStyle($step['layout_style'] ?? null),
                    'template' => $this->normalizeImportedTemplateKey($step['template'] ?? null),
                    'template_data' => is_array($step['template_data'] ?? null) ? $step['template_data'] : [],
                    'step_tags' => $this->normalizeStringArray($step['step_tags'] ?? []),
                    'background_color' => $this->nullableString($step['background_color'] ?? null),
                    'button_color' => $this->nullableString($step['button_color'] ?? null),
                    'layout_json' => $this->normalizeImportedLayoutJson($step['layout_json'] ?? ($step['layout'] ?? null)),
                ]);
            }

            return $template->fresh('steps');
        });
    }

    public function generateUniqueTemplateSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'template';
        $slug = $base;
        $counter = 1;

        while (
            FunnelTemplate::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function generateUniqueFunnelSlug(string $name, int $tenantId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'funnel';
        $slug = $base;
        $counter = 1;

        while (
            Funnel::query()
                ->where('tenant_id', $tenantId)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function generateUniqueStepSlug(Funnel $funnel, string $source, int $fallbackPosition = 1, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : 'step-' . $fallbackPosition;
        $slug = $base;
        $counter = 1;

        while (
            FunnelStep::query()
                ->where('funnel_id', $funnel->id)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function extractImportedSteps(array $payload): array
    {
        if (is_array($payload['steps'] ?? null)) {
            return array_values(array_filter($payload['steps'], 'is_array'));
        }

        if (is_array($payload['pages'] ?? null)) {
            return array_values(array_filter($payload['pages'], 'is_array'));
        }

        if (is_array($payload['layout_json'] ?? null) || is_array($payload['layout'] ?? null)) {
            return [[
                'title' => $payload['title'] ?? $payload['name'] ?? 'Landing',
                'slug' => $payload['slug'] ?? 'landing',
                'type' => $payload['type'] ?? 'landing',
                'content' => $payload['content'] ?? null,
                'cta_label' => $payload['cta_label'] ?? null,
                'template' => $payload['template'] ?? 'simple',
                'template_data' => $payload['template_data'] ?? [],
                'step_tags' => $payload['step_tags'] ?? [],
                'background_color' => $payload['background_color'] ?? null,
                'button_color' => $payload['button_color'] ?? null,
                'layout_style' => $payload['layout_style'] ?? null,
                'layout_json' => $payload['layout_json'] ?? $payload['layout'] ?? ['root' => [], 'sections' => []],
            ]];
        }

        return [];
    }

    private function normalizeImportedStepType(mixed $value): string
    {
        $type = trim((string) $value);
        return array_key_exists($type, FunnelStep::TYPES) ? $type : 'custom';
    }

    private function normalizeImportedLayoutStyle(mixed $value): ?string
    {
        $style = trim((string) $value);
        return array_key_exists($style, FunnelStep::LAYOUTS) ? $style : null;
    }

    private function normalizeImportedTemplateKey(mixed $value): string
    {
        $template = trim((string) $value);
        return array_key_exists($template, FunnelStep::TEMPLATES) ? $template : 'simple';
    }

    private function normalizeImportedPrice(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeImportedLayoutJson(mixed $value): array
    {
        return is_array($value) ? $value : ['root' => [], 'sections' => []];
    }

    private function normalizeStringArray(mixed $value): array
    {
        $values = is_array($value) ? $value : [];

        return collect($values)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    private function generateUniqueImportedTemplateStepSlug(FunnelTemplate $template, string $source, int $fallbackPosition = 1, ?int $ignoreId = null): string
    {
        $base = Str::slug($source);
        $base = $base !== '' ? $base : 'step-' . $fallbackPosition;
        $slug = $base;
        $counter = 1;

        while (
            $template->steps()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
