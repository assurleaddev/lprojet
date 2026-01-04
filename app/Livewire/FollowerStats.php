<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class FollowerStats extends Component
{
    public User $user;
    public int $followersCount = 0;
    public int $followingCount = 0;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->refreshCounts();
    }

    public function refreshCounts()
    {
        $this->followersCount = $this->user->followers()->count();
        $this->followingCount = $this->user->followings()->count();
    }

    #[On('follower-updated')]
    public function handleFollowerUpdate($userId)
    {
        if ($this->user->id === $userId) {
            $this->user->refresh();
            $this->refreshCounts();
        }
    }

    public function render()
    {
        return view('livewire.follower-stats');
    }
}
