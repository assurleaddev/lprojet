<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class FollowButton extends Component
{
    public User $user;
    public bool $isFollowing = false;
    public int $followersCount = 0;
    public int $followingCount = 0;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->followersCount = $user->followers_count ?? $user->followers()->count();
        $this->followingCount = $user->followings()->count();

        if (auth()->check()) {
            $this->isFollowing = auth()->user()->isFollowing($user);
        }
    }

    public function toggleFollow()
    {
        if (!auth()->check()) {
            $this->dispatch('open-login-popup');
            return;
        }

        if (auth()->id() === $this->user->id) {
            return; // Can't follow yourself
        }

        if ($this->isFollowing) {
            auth()->user()->unfollow($this->user);
            $this->isFollowing = false;
            $this->followersCount--;
        } else {
            auth()->user()->follow($this->user);
            $this->isFollowing = true;
            $this->followersCount++;
        }

        // Dispatch event to update follower stats
        $this->dispatch('follower-updated', userId: $this->user->id);
    }

    public function render()
    {
        return view('livewire.follow-button');
    }
}
