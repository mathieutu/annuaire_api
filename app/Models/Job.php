<?php

namespace App\Models;

// Extends are done in ApiModel such as Illuminate\Database\Eloquent\Model

/**
 * App\Models\Job
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $description
 * @property \Carbon\Carbon $from
 * @property \Carbon\Carbon $to
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereTitle($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereDescription($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereFrom($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereTo($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Job whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Job extends ApiModel
{

    public $timestamps = true;
    protected $table = 'jobs';
    protected $fillable = ['title', 'description', 'from', 'to'];
    protected $hidden = ['id', 'user_id'];
    protected $dates = ['from', 'to', 'created_at', 'updated_at'];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
