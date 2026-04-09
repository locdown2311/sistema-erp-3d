<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarController extends Controller
{
    public function index()
    {
        // For the initial load, we can pass the current month's tasks to Vue
        // to avoid an immediate secondary API call.
        $month = now()->month;
        $year = now()->year;

        $initialTasks = Task::where('user_id', auth()->id())
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->orderBy('due_date')
            ->get();

        return Inertia::render('Calendar/Index', [
            'initialTasks' => $initialTasks,
        ]);
    }

    public function tasks(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $tasks = Task::where('user_id', auth()->id())
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->orderBy('due_date')
            ->get();

        return response()->json($tasks);
    }

    public function storeTask(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'color' => 'nullable|string|max:7',
        ]);

        $task = Task::create(array_merge($validated, ['user_id' => auth()->id()]));
        return response()->json($task, 201);
    }

    public function updateTask(Request $request, Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:low,medium,high',
            'status' => 'required|in:pending,in_progress,completed',
            'color' => 'nullable|string|max:7',
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    public function destroyTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) abort(403);
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }
}
