<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use App\Thread;
use App\Reply;
use App\Channel;

class ReadThreadsTest extends TestCase
{
    use DatabaseMigrations;

    private $thread;

    public function setUp()
    {
        parent::setUp();
        $this->thread = factory(Thread::class)->create();
    }
    /*
    |
    | Para cada teste sera migrado (migrate)
    | e após será revertido (rollback)
    |
    */

    /** @test  */
    public function a_user_can_browse_threads()
    {
        $this->get('/threads')
             ->assertSee($this->thread->title);
    }

    /** @test  */
    public function a_user_read_a_single_thread()
    {
        $this->get($this->thread->path())
             ->assertSee($this->thread->title);
    }

    /** @test */
    public function a_user_can_read_replies_that_are_associated_with_a_thread()
    {
        $reply = factory(Reply::class)->create(['thread_id' => $this->thread->id]);

        $this->get($this->thread->path())
            ->assertSee($reply->body);
    }

    /** @test */
    public function a_user_can_filter_threads_according_to_a_channel()
    {
        $channel = factory(Channel::class)->create();

        $threadInChannel = factory(Thread::class)->create([
            'channel_id' => $channel->id
        ]);
        
        $threadNotInChannel = factory(Thread::class)->create();

        $this->get("/threads/{$channel->slug}")
             ->assertSee($threadInChannel->title)
             ->assertDontSee($threadNotInChannel->title);

    }
}