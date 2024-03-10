<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    /**
     * Test User Can View All Tasks.
     *
     * @return void
     */
    public function test_user_can_view_all_tasks()
    {
        // Create a user
        $user = User::factory()->create();

        // Create some tasks for the user
        Task::factory(5)->create(['user_id' => $user->id]);

        // Authenticate the user
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/tasks');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'due_date',
                        'user'
                    ]
                ],
                'page',
                'size',
                'total'
            ]);
    }

    /**
     * Test User Can View Specific Task.
     *
     * @return void
     */
    public function test_user_can_view_specific_task()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a task for the user
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        $response = $this->actingAs($user, 'sanctum')->getJson("/api/v1/tasks/{$task->id}");

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'user'
                ]
            ]);
    }

    /**
     * Test User Can Create Task.
     *
     * @return void
     */
    public function test_user_can_create_task()
    {
        // Create a user
        $user = User::factory()->create();

        // Task data
        $taskData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => 'new',
            'due_date' => Date::now()->addDays(7)->toDateString(),
            'user_id' => $user->id,
        ];

        // Authenticate the user
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/tasks', $taskData);

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'user'
                ]
            ]);
    }

    /**
     * Test User Can Update Task.
     *
     * @return void
     */
    public function test_user_can_update_task()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a task for the user
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Updated task data
        $updateData = [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'status' => 'done',
            'due_date' => Date::now()->addDays(10)->toDateString(),
            'user_id' => $user->id,
        ];

        // Authenticate the user
        $response = $this->actingAs($user, 'sanctum')->putJson("/api/v1/tasks/{$task->id}", $updateData);

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'due_date',
                    'user'
                ]
            ]);
    }

    /**
     * Test User Can Delete Task.
     *
     * @return void
     */
    public function test_user_can_delete_task()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a task for the user
        $task = Task::factory()->create(['user_id' => $user->id]);

        // Authenticate the user
        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/tasks/{$task->id}");

        // Assert response
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Task deleted successfully'
            ]);
    }

    /**
     * Test User Can View Deleted Tasks.
     *
     * @return void
     */
    public function test_user_can_view_deleted_tasks()
    {
        // Create an admin user
        $admin = User::factory()->create(['role' => 'admin']);

        // Create some deleted tasks
        Task::factory(5)->create(['is_deleted' => true]);

        // Authenticate the admin
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/tasks/deleted');

        // Assert response
        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'due_date',
                        'user'
                    ]
                ],
                'page',
                'size',
                'total'
            ]);
    }
}
