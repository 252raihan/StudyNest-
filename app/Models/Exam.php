<?php

namespace App\Models;

use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    /** @use HasFactory<ExamFactory> */
    use HasFactory;

    public const TYPE_MIDTERM = 'midterm';

    public const TYPE_FINAL = 'final';

    /**
     * Exam types recognised by the application.
     *
     * Kept here rather than in the database schema so that universities with
     * different exam naming systems can extend this list without a migration.
     *
     * @var list<string>
     */
    public const TYPES = [
        self::TYPE_MIDTERM,
        self::TYPE_FINAL,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'course_id',
        'type',
        'title',
    ];

    /**
     * The course this exam belongs to.
     *
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The topics covered by this exam, in their defined order.
     *
     * @return HasMany<Topic, $this>
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }
}
