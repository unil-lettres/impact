<?php

namespace App\Livewire;

use App\Card;
use App\File;
use App\Policies\AttachmentPolicy;
use App\Scopes\HideAttachmentsScope;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Attachments extends Component
{
    public $card;

    public function mount(Card $card)
    {
        $this->card = $card;
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function delete($id)
    {
        Validator::make(
            ['id' => $id],
            ['id' => 'required|integer|exists:files,id'],
        )->validate();

        $attachment = File::withoutGlobalScope(HideAttachmentsScope::class)
            ->findOrFail($id);

        $this->authorize('forceDelete', [
            AttachmentPolicy::class,
            $attachment,
        ]);

        // Delete the record from the database. The binary
        // file will be deleted with the FileObserver "deleted" event.
        $attachment->forceDelete();
    }

    public function render()
    {
        return view('livewire.attachments');
    }
}
