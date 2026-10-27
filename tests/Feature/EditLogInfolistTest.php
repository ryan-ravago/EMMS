<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\RelationManagers\EditLogsRelationManager;
use App\Models\AssetEditLog;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Tests\TestCase;

class EditLogInfolistTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $changes
     */
    private function render(array $changes): string
    {
        Filament::setCurrentPanel('admin');

        $relationManager = new EditLogsRelationManager;
        $log = new AssetEditLog(['changes' => $changes, 'logged_at' => '2026-10-26 16:41:00']);

        return $relationManager->infolist(Schema::make($relationManager))->record($log)->toHtml();
    }

    public function test_changes_are_shown_in_a_filament_table_with_old_and_new_values(): void
    {
        $html = $this->render([
            'eqm_is_active' => ['old' => true, 'new' => false],
            'eqm_plate_num' => ['old' => null, 'new' => 'UIB 724'],
        ]);

        $this->assertStringContainsString('fi-in-table-repeatable', $html);
        $this->assertStringContainsString('Old Value', $html);
        $this->assertStringContainsString('New Value', $html);
        $this->assertStringContainsString('Active', $html);
        $this->assertStringContainsString('Plate No.', $html);
        $this->assertStringContainsString('UIB 724', $html);
        $this->assertStringContainsString('fi-color-danger', $html);
        $this->assertStringContainsString('fi-color-success', $html);
    }

    public function test_the_hand_built_html_table_is_gone(): void
    {
        $html = $this->render(['eqm_name' => ['old' => 'A', 'new' => 'B']]);

        $this->assertStringNotContainsString('uppercase tracking-wide', $html);
        $this->assertStringNotContainsString('<table class=', $html);
    }

    public function test_values_are_escaped_and_never_rendered_as_html(): void
    {
        $html = $this->render(['eqm_name' => ['old' => '<b>old</b>', 'new' => '<script>alert(1)</script>']]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<b>old</b>', $html);
    }

    public function test_multi_value_fields_such_as_tags_list_every_value(): void
    {
        $html = $this->render(['categories' => ['old' => [], 'new' => ['30 TONS', 'CRANE']]]);

        $this->assertStringContainsString('Tags', $html);
        $this->assertStringContainsString('30 TONS', $html);
        $this->assertStringContainsString('CRANE', $html);
    }

    public function test_a_log_without_changes_says_so(): void
    {
        $this->assertStringContainsString('No changes recorded', $this->render([]));
    }
}
