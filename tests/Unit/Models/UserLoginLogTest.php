<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Story 7.1 — Modelo UserLoginLog.
 */
class UserLoginLogTest extends TestCase
{
    public function test_fillable_inclui_campos_necessarios(): void
    {
        $log = new UserLoginLog();
        $this->assertContains('user_id', $log->getFillable());
        $this->assertContains('logged_in_at', $log->getFillable());
        $this->assertContains('ip_address', $log->getFillable());
    }

    public function test_logged_in_at_e_cast_para_datetime(): void
    {
        $log = UserLoginLog::make(['logged_in_at' => '2026-06-01 10:00:00']);
        $this->assertInstanceOf(Carbon::class, $log->logged_in_at);
    }

    public function test_relacao_user_e_belongs_to(): void
    {
        $log = new UserLoginLog();
        $relation = $log->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }
}
