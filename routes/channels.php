<?php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('forum.global', fn($user) => !is_null($user));

Broadcast::channel('presence-forum', fn($user) => ['id' => $user->id, 'name' => $user->name]);

Broadcast::channel('user.{id}', fn($user, $id) => (int) $user->id === (int) $id);
