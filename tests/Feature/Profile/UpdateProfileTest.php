<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('users can update their profile', function () {

    $user = User::factory()->create();
    auth()->login($user);

    $newName = $user->name . " edit";
    $response = $this->put('/api/users/profile/update', [
        'name' => $newName,
        'avatar' => UploadedFile::fake()->image('avatar.jpg')
    ]);

    $avatarFileName = $user->id . "-" . now()->format('d_m_y_h_i_s');
    expect($user->name)->toBe($newName);
    expect($user->avatar)->toBe($avatarFileName);

    Storage::disk('public')->assertExists($user->avatarPath());
    $response->assertStatus(200)
             ->assertJson(['success' => true]);

});
