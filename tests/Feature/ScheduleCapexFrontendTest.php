<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScheduleCapexFrontendTest extends TestCase
{
    public function test_capex_schedule_uses_budget_dashboard_frontend(): void
    {
        $view = $this->view('modules.schedule-content', [
            'workType' => 'capex',
            'workTypeLabel' => 'Rencana Kerja Capex',
        ]);

        $view
            ->assertSeeInOrder([
                'Dashboard',
                'Lanjut',
                'Pending',
                'Batal',
                'Semua',
                'Pagu &amp; Ajuan Anggaran',
                'Rp 15,8 M',
                '56',
                'Ajuan Plant',
                'Rencana Kerja Capex',
                'Distribusi Pagu',
                'Remarks',
            ], false)
            ->assertSee('Pagination remarks Capex')
            ->assertDontSee('Kurva S - Progress')
            ->assertDontSee('Gantt Chart - Progress');
    }
}
