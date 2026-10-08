<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Documento allegato a una notifica (core.notification_attachments).
 *
 * @property string $id
 * @property string $file_path
 * @property string $notification_id
 */
#[Table('core.notification_attachments')]
#[Fillable(['file_path', 'notification_id'])]
class NotificationAttachment extends Model
{
    use HasUuids;

    /** La tabella ha solo created_at. */
    public const UPDATED_AT = null;

    /** @return BelongsTo<Notification, $this> */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class);
    }
}
