<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TaskController
{
    /**
     * Get all tasks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        if ($request->user()->role === 'admin') {
            $tasks = Task::where('is_deleted', false)->paginate($size, ['*'], 'page', $page);
        } else {
            $tasks = Task::where('user_id', $request->user()->id)->where('is_deleted', false)->paginate($size, ['*'], 'page', $page);
        }

        return ApiResponse::success(message: "Tasks retrieved successfully", data: TaskResource::collection($tasks), page: $tasks->currentPage(), size: $tasks->perPage(), total: $tasks->total());
    }

    /**
     * Get task by id
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, int $id)
    {
        $task = Task::where('id', $id)->where('is_deleted', false)->first();
        if (!$task) {
            return ApiResponse::error('Task not found', 'task:not-found', 404);
        }

        if ($task->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return ApiResponse::error('You are not authorized to view this task', 'task:unauthorized', 403);
        }

        return ApiResponse::success(message: "Task retrieved successfully", data: new TaskResource($task));
    }

    /**
     * Create a new task
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Create a new task with the user_id of the authenticated user if the user is not an admin, otherwise use the user_id from the request
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'in:new,pending,done',
            'due_date' => 'date',
            'user_id' => 'exists:users,id'
        ], [
            'title.required' => 'The title field is required',
            'title.string' => 'The title field must be a string',
            'description.required' => 'The description field is required',
            'description.string' => 'The description field must be a string',
            'due_date.date' => 'The due_date field must be a date',
            'status.in' => 'The status field must be one of: new, pending, done',
            'user_id.exists' => 'The user_id does not exist'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 'validation:failed', 422, $validator->errors());
        }

        $task = new Task();
        $task->title = $request->title;
        $task->description = $request->description;
        if ($request->status) {
            $task->status = $request->status;
        }
        if ($request->due_date) {
            $task->due_date = $request->due_date;
        } else {
            $task->due_date = now();
        }
        if ($request->user()->role === 'admin') {
            $task->user_id = $request->user_id;
        } else {
            $task->user_id = $request->user()->id;
        }
        $task->save();

        return ApiResponse::success(message: "Task created successfully", data: new TaskResource($task));
    }

    /**
     * Update a task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'status' => 'in:new,pending,done',
            'due_date' => 'date',
            'user_id' => 'exists:users,id'
        ], [
            'title.required' => 'The title field is required',
            'title.string' => 'The title field must be a string',
            'description.required' => 'The description field is required',
            'description.string' => 'The description field must be a string',
            'due_date.date' => 'The due_date field must be a date',
            'status.in' => 'The status field must be one of: new, pending, done',
            'user_id.exists' => 'The user_id does not exist'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 'validation:failed', 422, $validator->errors());
        }

        $task = Task::where('id', $id)->where('is_deleted', false)->first();
        if (!$task) {
            return ApiResponse::error('Task not found', 'task:not-found', 404);
        }

        if ($task->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return ApiResponse::error('You are not authorized to update this task', 'task:unauthorized', 403);
        }

        $task->title = $request->title;
        $task->description = $request->description;
        if ($request->due_date) {
            $task->due_date = $request->due_date;
        } else {
            $task->due_date = now();
        }
        if ($request->status) {
            $task->status = $request->status;
        }
        if ($request->user()->role === 'admin') {
            $task->user_id = $request->user_id;
        } else {
            $task->user_id = $request->user()->id;
        }
        $task->save();

        return ApiResponse::success(message: "Task updated successfully", data: new TaskResource($task));
    }

    /**
     * Delete a task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, int $id)
    {
        $task = Task::where('id', $id)->where('is_deleted', false)->first();
        if (!$task) {
            return ApiResponse::error('Task not found', 'task:not-found', 404);
        }

        if ($task->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return ApiResponse::error('You are not authorized to delete this task', 'task:unauthorized', 403);
        }

        $task->is_deleted = true;
        $task->save();

        return ApiResponse::success(message: "Task deleted successfully");
    }

    /**
     * Deleted tasks
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleted(Request $request)
    {
        // Only the admin can see the deleted tasks
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);

        if ($request->user()->role !== 'admin') {
            return ApiResponse::error('You are not authorized to view this page', 'task:unauthorized', 403);
        }

        $tasks = Task::where('is_deleted', true)->paginate($size, ['*'], 'page', $page);

        return ApiResponse::success(message: "Deleted tasks retrieved successfully", data: TaskResource::collection($tasks), page: $tasks->currentPage(), size: $tasks->perPage(), total: $tasks->total());
    }
}
