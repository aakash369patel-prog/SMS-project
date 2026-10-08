<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;

class MessageThreadController extends Controller
{
    public function index()
    {
        return response()->json(
            MessageThread::query()
                ->with(['parentGuardian', 'messages' => fn ($q) => $q->latest()->limit(1)])
                ->latest()
                ->paginate(20)
        );
    }

    public function show(MessageThread $messageThread)
    {
        $messageThread->load(['parentGuardian', 'messages.sender', 'messages.moderator']);

        return response()->json(['data' => $messageThread]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id' => ['required', 'exists:parents,id'],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
        ]);

        $user = $request->user();
        $thread = MessageThread::query()->create([
            'parent_id' => $data['parent_id'],
            'subject' => $data['subject'],
            'created_by' => $user->id,
        ]);

        $this->createMessage($thread, $user, $data['body']);

        return response()->json(['data' => $thread->load(['parentGuardian', 'messages.sender'])], 201);
    }

    public function reply(Request $request, MessageThread $messageThread)
    {
        $data = $request->validate(['body' => ['required', 'string']]);

        $message = $this->createMessage($messageThread, $request->user(), $data['body']);

        return response()->json(['data' => $message->load('sender')], 201);
    }

    public function moderate(Request $request, Message $message)
    {
        $data = $request->validate([
            'moderation_status' => ['required', 'in:approved,rejected'],
        ]);

        $approved = $data['moderation_status'] === 'approved';
        $message->update([
            'moderation_status' => $data['moderation_status'],
            'moderated_by' => $request->user()->id,
            'moderated_at' => now(),
            'visible_to_parent' => $approved,
        ]);

        return response()->json(['data' => $message->fresh(['sender', 'moderator'])]);
    }

    private function createMessage(MessageThread $thread, User $user, string $body): Message
    {
        [$status, $visible] = $this->moderationDefaults($user);

        return Message::query()->create([
            'message_thread_id' => $thread->id,
            'sender_user_id' => $user->id,
            'body' => $body,
            'moderation_status' => $status,
            'moderated_at' => $visible ? now() : null,
            'moderated_by' => $visible ? $user->id : null,
            'visible_to_parent' => $visible,
        ]);
    }

    /** @return array{0: string, 1: bool} */
    private function moderationDefaults(User $user): array
    {
        if ($user->isParent()) {
            return ['approved', true];
        }
        if ($user->isTeacher()) {
            return ['pending', false];
        }

        return ['approved', true];
    }
}
