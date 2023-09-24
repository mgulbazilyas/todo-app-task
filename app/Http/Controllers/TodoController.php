<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// add todo model
class TodoController extends Controller
{
    
    public function list(Request $request)
    {
        $query = Todo::where('user_id', auth()->id());

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', "%{$searchTerm}%");
        }
    
        return response()->json(['data' => $query->orderBy('updated_at', 'desc')->paginate(10)]);    }
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
        ]);

        $todo = new Todo([
            // 'user_id' => Auth::id(),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => '0',
        ]);
        $todo->user_id = Auth::id(); // Assuming you have user authentication

        $todo->save();
        return response()->json(['message' => 'ToDo created successfully']);
    }
    public function view($id)
    {
        $todo = Todo::findOrFail($id);

        if ($todo->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($todo);
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'status' => 'int'
        ]);

        $todo = Todo::findOrFail($id);

        if ($todo->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $status = $request->filled('status') ? $request->input('status') : $todo->status;

        $todo->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'status' => $status,
        ]);

        return response()->json(['message' => 'ToDo updated successfully']);
    }
    public function delete($id)
    {
        $todo = Todo::findOrFail($id);

        if ($todo->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $todo->delete();

        return response()->json(['message' => 'ToDo deleted successfully']);
    }
}
