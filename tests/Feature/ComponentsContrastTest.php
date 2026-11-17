<?php

namespace Tests\Feature;

use Tests\TestCase;

class ComponentsContrastTest extends TestCase
{
    public function test_card_and_stat_card_render_with_accessible_contrast(): void
    {
        $view = $this->blade('<x-card title="Test Card" subtitle="Test Subtitle">Content</x-card>');
        $view->assertSee('text-zinc-900 dark:text-zinc-100');
        $view->assertSee('text-zinc-500 dark:text-zinc-400');

        $statView = $this->blade('<x-stat-card title="Online Users" value="123" change="+5 today" />');
        $statView->assertSee('text-zinc-500 dark:text-zinc-400');
        $statView->assertSee('text-2xl font-bold text-zinc-900 dark:text-zinc-100');
    }
}
