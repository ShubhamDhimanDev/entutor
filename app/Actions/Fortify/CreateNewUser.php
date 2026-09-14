<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'role' => ['nullable', Rule::enum(UserRole::class)],
        ])->validate();

        $role = isset($input['role']) && $input['role'] !== ''
            ? UserRole::from($input['role'])
            : UserRole::Learner;

        return DB::transaction(function () use ($input, $role): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => $role,
            ]);

            if ($role === UserRole::Learner) {
                $user->learnerProgram()->create(['start_date' => now()->toDateString()]);
            }

            return $user;
        });
    }
}
