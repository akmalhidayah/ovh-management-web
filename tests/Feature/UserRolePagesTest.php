<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_qc_pages_render_for_qc_user(): void
    {
        $user = User::factory()->make([
            'name' => 'User QC',
            'email' => 'qc@ovh.test',
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($user);

        foreach ([
            'user.qc.dashboard',
            'user.qc.forms.create',
            'user.qc.drafts.index',
            'user.qc.history.index',
            'user.qc.profile',
        ] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('user.qc.documents.index'))->assertRedirect(route('user.qc.history.index'));
    }

    public function test_other_roles_render_their_own_pages(): void
    {
        $roles = [
            'commissioning' => [
                'routes' => [
                    'user.commissioning.dashboard',
                    'user.commissioning.forms.create',
                    'user.commissioning.drafts.index',
                    'user.commissioning.history.index',
                    'user.commissioning.profile',
                ],
                'redirects' => [
                    'user.commissioning.documents.index' => 'user.commissioning.history.index',
                ],
            ],
            'pgo' => [
                'routes' => [
                    'user.pgo.dashboard',
                    'user.pgo.tasks.index',
                    'user.pgo.monitoring.index',
                    'user.pgo.documents.index',
                    'user.pgo.history.index',
                    'user.pgo.profile',
                ],
            ],
            'approval' => [
                'routes' => [
                    'user.approval.dashboard',
                    'user.approval.pending.index',
                    'user.approval.review.index',
                    'user.approval.history.index',
                    'user.approval.documents.index',
                    'user.approval.profile',
                ],
            ],
        ];

        foreach ($roles as $role => $config) {
            $user = User::factory()->make([
                'email' => "{$role}@ovh.test",
                'usertype' => 'user',
                'role' => $role,
            ]);

            $this->actingAs($user);

            foreach ($config['routes'] as $route) {
                $this->get(route($route))->assertOk();
            }

            foreach (($config['redirects'] ?? []) as $route => $target) {
                $this->get(route($route))->assertRedirect(route($target));
            }
        }
    }

    public function test_user_is_redirected_to_own_dashboard_when_opening_other_role_route(): void
    {
        $qcUser = User::factory()->make([
            'usertype' => 'user',
            'role' => 'qc',
        ]);

        $this->actingAs($qcUser);

        $this->get(route('user.commissioning.dashboard'))->assertRedirect(route('user.qc.dashboard'));
        $this->get(route('user.pgo.dashboard'))->assertRedirect(route('user.qc.dashboard'));
        $this->get(route('user.approval.dashboard'))->assertRedirect(route('user.qc.dashboard'));
    }

    public function test_legacy_inspector_routes_redirect_to_qc_pages(): void
    {
        $this->get('/inspector/dashboard')->assertRedirect('/user/qc/dashboard');
        $this->get('/inspector/forms/create')->assertRedirect('/user/qc/forms/create');
        $this->get('/inspector/documents')->assertRedirect('/user/qc/history');
    }
}
