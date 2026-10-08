<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SchoolManagementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSchoolManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SchoolManagementSeeder::class);
    }

    public function test_dashboard_includes_stats_and_charts(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@school.local')->firstOrFail());

        $response = $this->getJson('/api/v1/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'stats' => ['students', 'teachers', 'open_fees'],
                'charts' => ['attendance_trends', 'fee_collection', 'pass_rates'],
            ]);
    }

    public function test_students_can_be_filtered_by_search(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'admin@school.local')->firstOrFail());

        $this->getJson('/api/v1/students?q=John')
            ->assertOk()
            ->assertJsonPath('data.0.first_name', 'John');
    }

    public function test_student_fees_support_status_filter(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'staff@school.local')->firstOrFail());

        $this->getJson('/api/v1/student-fees?status=partial')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_attendance_history_supports_date_range(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'teacher@school.local')->firstOrFail());

        $from = now()->subDays(7)->toDateString();
        $to = now()->toDateString();

        $this->getJson("/api/v1/attendance?from={$from}&to={$to}")
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_parent_can_access_fee_statements(): void
    {
        Sanctum::actingAs(User::query()->where('email', 'parent@school.local')->firstOrFail());

        $this->getJson('/api/v1/parent/fees')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
