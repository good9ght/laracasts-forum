<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Events\ThreadHasNewReply;
use Laravel\Scout\Searchable;
use Stevebauman\Purify\Purify;

class Thread extends Model
{
    use RecordsActivity, Searchable;

    protected $guarded = ['id'];
    protected $with = ['channel', 'creator'];
    protected $appends = ['isSubscribedTo'];
    protected $casts = [
        'locked' => 'boolean'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });

        static::created(function ($thread) {
            $thread->update([
                'slug' => $thread->title
            ]);
        });
    }
    public function searchableAs()
    {
        return 'threads_index';
    }

    /**
     * A thread belongs to a creator.
     *
     * @return void
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * A thread belongs to a channel.
     *
     * @return void
     */
    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    /**
     * A thread has replies.
     *
     * @return void
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * A thread can have subscriptions
     *
     * @return void
     */
    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    /**
     * A thread can notify its subscribers about a new reply
     *
     * @return void
     */
    public function notifySubscribers($reply)
    {
        $this->subscriptions
            ->where('user_id', '!=', $reply->user_id)
            ->each
            ->notify($reply);
    }

    /**
     * Returns the uri of the current thread.
     *
     * @return string
     */
    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
    }

    /**
     * Creates a reply to the current thread.
     *
     * @param array $reply
     * @return mixed
     */
    public function addReply($reply)
    {
        $reply = $this->replies()->create($reply);

        ThreadHasNewReply::dispatch($this, $reply);

        return $reply;
    }

    /**
     * Applies filters to the thread's query.
     *
     * @param Illuminate\Database\Query\Builder  $query
     * @param App\Filters\ThreadFilter $filters
     * @return void
     */
    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    /**
     * A thread can be subscribed to.
     *
     * @param int $userId
     * @return App\thread $this
     */
    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: auth()->id()
        ]);

        return $this;
    }

    /**
     * A thread can be unsubscribed from.
     *
     * @param int $userId
     * @return void
     */
    public function unsubscribe($userId = null)
    {
        $this->subscriptions()->where([
            'user_id' => $userId ?: auth()->id()
        ])->delete();
    }


    /**
     *
     * Return if the current user is subscribed to the thread
     *
     * @return mixed
     */
    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()->where('user_id', auth()->id())->exists();
    }

    /**
     * @param $user
     * @return bool
     * @throws \Exception
     */
    public function hasUpdatesFor($user = null)
    {
        $user = $user ?? auth()->user();

        $key = $user->visitedThreadCacheKey($this);

        return $this->updated_at > cache($key);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     *
     * Set the proper slug attribute
     *
     * @param $value
     */
    public function setSlugAttribute($value)
    {
        $slug = str_slug($value);
        $original = $slug;
        $count = 2;

        while (static::whereSlug($slug)->exists()) {
            $slug = "{$original}-".$count++;
        }

        $this->attributes['slug'] = $slug;
    }

    /**
     *
     * A thread can have a best reply.
     *
     * @param Reply $reply
     */
    public function markBestReply(Reply $reply)
    {
        $this->best_reply_id = $reply->id;

        $this->save();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return $this->toArray() + [ 'path' => $this->path() ];
    }

    public function getBodyAttribute($body)
    {
        return (new Purify)->clean($body);
    }
}
