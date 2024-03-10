<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Task
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $status
 * @property int|null $user_id
 * @property Carbon $due_date
 * @property bool|null $is_deleted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property User|null $user
 *
 * @package App\Models
 */
class Task extends Model
{
    use HasFactory;
	protected $table = 'tasks';

	protected $casts = [
		'user_id' => 'int',
		'due_date' => 'datetime',
		'is_deleted' => 'bool'
	];

	protected $fillable = [
		'title',
		'description',
		'status',
		'user_id',
		'due_date',
		'is_deleted'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
