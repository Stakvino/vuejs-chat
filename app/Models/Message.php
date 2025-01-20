<?php

namespace App\Models;

use App\Models\User;
use App\Models\Channel;
use App\Models\FileMessage;
use App\Models\MessageSeen;
use App\Models\MessageType;
use App\Models\AudioMessage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get the messages tha the user sent.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * Get the user that sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user that sent this message.
     */
    public function seens(): HasMany
    {
        return $this->hasMany(MessageSeen::class);
    }

    /**
     * Get the type of the message (text, file attachment, audio...).
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(MessageType::class, 'message_type_id');
    }

    /**
     * Get the file that was attached to the message.
     */
    public function file(): HasOne
    {
        return $this->hasOne(FileMessage::class);
    }

    /**
     * Get the audio that was sent as a message.
     */
    public function audio(): HasOne
    {
        return $this->hasOne(AudioMessage::class);
    }

    /**
     * Check if message was a file attachment.
     */
    public function isFile(): bool
    {
        return $this->type->id === MessageType::FILE_ID;
    }

    /**
     * Check if message was an image.
     */
    public function isImage(): bool
    {
        return $this->isFile() && $this->file->is_image;
    }

    /**
     * Check if message was an audio message.
     */
    public function isAudio(): bool
    {
        return $this->type->id === MessageType::AUDIO_ID;
    }

    /**
     * Get the all users that are subscribed to the channel.
     */
    public function sender(): User
    {
        $sender = $this->user()->select('name', 'personal_color', 'users.id', 'users.avatar')->first();
        $sender->avatar_path = $sender->avatarPath();
        return $sender;
    }

    /**
     * Check if this message was sent by the signed user.
     */
    public function isMyMessage(): bool
    {
        return $this->sender()->id === auth()->user()->id;
    }

    /**
     * get the list of the users that saw the message.
     */
    public function usersSeen(): Collection
    {
        return User::join('message_seens', 'message_seens.user_id', 'users.id')
        ->where('message_seens.is_seen', true)
        ->where('message_seens.message_id', $this->id)
        ->whereNot('users.id', auth()->user()->id)
        ->select(
            'users.name', 'users.personal_color', 'users.id', 'message_seens.created_at as seen_created_at'
            // \DB::RAW('DATE_FORMAT(message_seens.created_at, "%d/%m/%Y %H:%i") as seen_created_at')
        )->get();
    }

    /**
     * Check if a user saw this message.
     */
    public function isSeenBy(User $user): bool
    {
        // #Stakvino
        return $this->seens()->where('user_id', $user->id)->first()->is_seen ?? false;
    }

    /**
     * Get the data neccessery for the front-end rendering of the message.
     */
    public function getInfo(): array
    {
        if ($this->isAudio()) {
            // dd($this->audio());
        }

        return [
            'format_created_at' => $this->created_at->format('h:i'),
            'isMyMessage' => $this->isMyMessage(),
            'usersSeen' => $this->usersSeen(),
            'isSeenByAuth' => $this->isSeenBy(auth()->user()),
            'sender' => $this->sender(),
            'is_file' => $this->isFile(),
            'is_image' => $this->isImage(),
            'is_audio' => $this->isAudio(),
            'file_path' => $this->isFile() ? $this->file->file_path : null,
            'audio_path' => $this->isAudio() ? $this->audio->file_path : null,
            'audio_duration' => $this->isAudio() ? $this->audio->duration : null
        ];
    }

    /**
     * Delete a message.
     */
    public function remove()
    {
        if ( $this->isFile() ) {
            // Storage::disk('public')->delete($this->file->file_path);
        }
        if ( $this->isAudio() ) {
            // Storage::disk('public')->delete($this->audio->file_path);
        }

        // return $this->delete();

        $this->update(['is_deleted' => true]);
    }

}
