<?php

namespace Tests\Unit;

use App\Rules\StrongPassword;
use PHPUnit\Framework\TestCase;

class StrongPasswordTest extends TestCase
{
    public function test_rejects_passwords_that_fail_a_criterion(): void
    {
        $this->assertFails('Ab1!', 'pelo menos 8');
        $this->assertFails('senhaforte1!', 'maiúscula');
        $this->assertFails('SENHAFORTE1!', 'minúscula');
        $this->assertFails('SenhaForte!', 'número');
        $this->assertFails('SenhaForte1', 'símbolo');
        $this->assertFails('SenhaForte1^', 'só pode conter');
    }

    public function test_accepts_passwords_meeting_all_criteria(): void
    {
        $this->assertPasses('SenhaForte@123');
        $this->assertPasses('Financi.ai2026');
        $this->assertPasses('A1!aaaaaaa');
    }

    private function assertFails(string $password, string $expectedFragment): void
    {
        $failure = null;
        (new StrongPassword)->validate('password', $password, function (string $message) use (&$failure) {
            $failure = $message;
        });

        $this->assertNotNull($failure, "Esperava que '{$password}' falhasse a validação.");
        $this->assertStringContainsString($expectedFragment, $failure);
    }

    private function assertPasses(string $password): void
    {
        $failed = false;
        (new StrongPassword)->validate('password', $password, function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed, "Esperava que '{$password}' passasse na validação.");
    }
}
