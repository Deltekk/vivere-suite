<?php

namespace Database\Factories;

use App\Enums\CourseYear;
use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Course;
use App\Models\School;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * Di default: studente universitario attivo, con mail UNIPA e username nel formato Nome.Cognome.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->firstName();
        $surname = fake()->lastName();
        // Formato UNIPA: NomeSenzaSpazi.Cognome + eventuale numero (es. MarioLuigi.Rossi03)
        $username = Str::studly(Str::ascii($name)).'.'.Str::studly(Str::ascii($surname)).fake()->unique()->numerify('##');

        return [
            'name' => $name,
            'surname' => $surname,
            'username' => $username,
            'birthday' => fake()->dateTimeBetween('-30 years', '-19 years'),
            'course_year' => fake()->randomElement([CourseYear::T1, CourseYear::T2, CourseYear::T3, CourseYear::M1, CourseYear::M2]),
            'course_year_confirmed_at' => now(), // altrimenti a ottobre verrebbe chiesta la conferma dell'anno
            'phone_number' => fake()->numerify('+39 3## ### ####'),
            'email' => Str::lower($username).'@community.unipa.it',
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'gender' => fake()->optional()->randomElement(Gender::cases()),
            'email_verified_at' => now(),
            'privacy_accepted_at' => now(),
            'terms_version' => config('vivere.terms_version'),
            'course_id' => Course::factory(),
            'role' => Role::Student,
            'status' => UserStatus::Active,
        ];
    }

    /**
     * Account appena registrato, in attesa di uno staffer.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => UserStatus::Pending]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }

    /**
     * Studente delle superiori: niente corso e mail non UNIPA.
     */
    public function highSchool(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_year' => fake()->randomElement([CourseYear::S4, CourseYear::S5]),
            'birthday' => fake()->dateTimeBetween('-19 years', '-16 years'),
            'email' => Str::lower($attributes['username']).'@example.com',
            'course_id' => null,
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::Staff]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::Admin]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => ['role' => Role::SuperAdmin]);
    }

    /**
     * Con il profilo staff già completo (codice fiscale, luogo di nascita, scuola): senza,
     * lo staff verrebbe mandato a completarlo prima di poter usare i panel.
     */
    public function withStaffProfile(): static
    {
        return $this
            ->state(fn (array $attributes) => ['school_id' => $attributes['school_id'] ?? School::factory()])
            ->afterCreating(fn (User $user) => StaffProfile::factory()->for($user)->create());
    }
}
