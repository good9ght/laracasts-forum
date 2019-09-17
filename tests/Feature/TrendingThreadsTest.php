<?php

namespace Tests\Feature;

use App\Thread;
use App\Trending;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrendingThreadsTest extends TestCase
{
    use RefreshDatabase;

    protected $trending;

    public function setUp(): void
    {
        parent::setUp();

        $this->trending = new Trending();

        $this->trending->reset();
    }

    /** @test */
    public function it_increments_a_thread_score_each_time_it_is_read()
    {
        $this->assertEmpty($this->trending->get());

        $thread = factory(Thread::class)->create();

        $this->call('GET', $thread->path());

        $this->assertCount(1, $this->trending->get());

        $this->assertEquals($thread->title, $this->trending->get()[0]->title);
    }
}
