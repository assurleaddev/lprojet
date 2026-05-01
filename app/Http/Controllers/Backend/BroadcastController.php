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
            'pre_text' => 'nullable|string|max:500',
            'body' => 'required|string|max:2000',
            'after_text' => 'nullable|string|max:500',
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
            'pre_text' => $data['pre_text'] ?? null,
            'body' => $data['body'],
            'after_text' => $data['after_text'] ?? null,
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

    public function edit(Broadcast $broadcast)
    {
        abort_if($broadcast->status !== 'draft', 403, 'Only drafts can be edited.');

        return view('backend.broadcasts.edit', compact('broadcast'));
    }

    public function update(Request $request, Broadcast $broadcast)
    {
        abort_if($broadcast->status !== 'draft', 403, 'Only drafts can be edited.');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'pre_text' => 'nullable|string|max:500',
            'body' => 'required|string|max:2000',
            'after_text' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:2048',
            'button_label' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('image')) {
            if ($broadcast->image_path) {
                Storage::disk('public')->delete($broadcast->image_path);
            }
            $data['image_path'] = $request->file('image')->store('broadcasts', 'public');
        }

        $broadcast->update([
            'title' => $data['title'],
            'pre_text' => $data['pre_text'] ?? null,
            'body' => $data['body'],
            'after_text' => $data['after_text'] ?? null,
            'image_path' => $data['image_path'] ?? $broadcast->image_path,
            'button_label' => $data['button_label'] ?? null,
            'button_url' => $data['button_url'] ?? null,
        ]);

        if ($request->input('action') === 'send') {
            SendBroadcastJob::dispatch($broadcast->id);

            return redirect()->route('admin.broadcasts.index')
                ->with('success', 'Broadcast queued and sending to all users.');
        }

        return redirect()->route('admin.broadcasts.index')
            ->with('success', 'Broadcast updated.');
    }

    public function send(Broadcast $broadcast)
    {
        abort_if($broadcast->status !== 'draft', 403, 'Only drafts can be sent.');

        SendBroadcastJob::dispatch($broadcast->id);

        return redirect()->route('admin.broadcasts.index')
            ->with('success', 'Broadcast queued and sending to all users.');
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
