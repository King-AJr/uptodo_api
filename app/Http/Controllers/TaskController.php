<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class TaskController extends Controller
{



    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Task::where('user_id', $user->id);


            if ($request->has('completed')) {
                $query->where('completed', $request->input('completed'));
            }


            if ($request->has('priority')) {
                $query->where('priority', $request->input('priority'));
            }


            if ($request->has('today') && $request->input('today') == true) {

                $today = Carbon::today()->toDateString();

                $query->whereDate('time', $today);
            }

            if ($request->has('date')) {
                $date = $request->input('date');
                $query->whereDate('time', $date);
            }

            $tasks = $query->with('category')->get();

            return response()->json(['success' => true, 'tasks' => $tasks]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to retrieve tasks', 'message' => $e->getMessage()], 500);
        }
    }

    // store(): Create a new task
    // structure title, description, time, priority, completed false by default, user_id, category_id
    public function store(Request $request)
    {
        Log::info($request->all());

        // Validate data
        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'time' => 'required|string',
                'priority' => 'required|integer',
                'category_id' => 'required|integer',
            ]);

            // Add user_id after validation
            $validated['user_id'] = Auth::user()->id;

            // Create the task
            $task = Task::create($validated);
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        try {
            $task = Task::findOrFail($id);

            return response()->json([
                'success' => true,
                'task' => $task
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);

            $validated = $request->validate([
                'title' => 'required|string',
                'description' => 'required|string',
                'time' => 'required|string',
                'priority' => 'required|integer',

                'category_id' => 'required|integer',
            ]);

            if ($request->has('completed')) $validated['completed'] = $request->completed;

            $validated['user_id'] = Auth::user()->id;

            $task->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $task = Task::findOrFail($id);

            $task->delete();

            return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'error' => 'Task not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Something went wrong please try again', 'message' => $e->getMessage()], 500);
        }
    }
}
