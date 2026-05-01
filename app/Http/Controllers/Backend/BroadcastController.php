<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Jobs\SendBroadcastJob;
use App\Models\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = Broadcast::latest()->paginate(20);

        return view('backend.broadcasts.index', compact('broadcasts'));
    }

    public function create()
    {
        return view('backend.broadcasts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'image' => 'nullable|image|max:2048',
            'button_label' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:500',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcasts', 'public');
        }

        $broadcast = Broadcast::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'image_path' => $imagePath,
            'button_label' => $data['button_label'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'status' => 'draft',
        ]);

        if ($request->input('action') === 'send') {
            SendBroadcastJob::dispatch($broadcast->id);

            return redirect()->route('admin.broadcasts.index')
                ->with('success', 'Broadcast queued and sending to all users.');
        }

        return redirect()->route('admin.broadcasts.index')
            ->with('success', 'Broadcast saved as draft.');
    }

    public function destroy(Broadcast $broadcast)
    {
        if ($broadcast->image_path) {
            Storage::disk('public')->delete($broadcast->image_path);
        }

        $broadcast->delete();

        return redirect()->route('admin.broadcasts.index')
            ->with('success', 'Broadcast deleted.');
    }
}
