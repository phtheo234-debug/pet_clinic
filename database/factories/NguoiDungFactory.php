<?php

namespace Database\Factories;

use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<NguoiDung>
 */
class NguoiDungFactory extends Factory
{
    private function generatePassword(string $username): string
    {
        $letters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $numbers = '23456789';
        $specials = '!@#$%*?_-';
        $all = $letters . $numbers . $specials;
        $characters = [
            $letters[random_int(0, strlen($letters) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $specials[random_int(0, strlen($specials) - 1)],
        ];
        while (count($characters) < 9) {
            $characters[] = $all[random_int(0, strlen($all) - 1)];
        }
        for ($i = count($characters) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }
        $password = implode('', $characters);

        return strcasecmp($password, $username) === 0
            ? $this->generatePassword($username)
            : $password;
    }

    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'username' => $username,
            'mat_khau' => Hash::make($this->generatePassword($username)),
            'ho_ten' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('09########'),
            'vaitro' => 'NHAN_VIEN',
            'trangthai' => 'HOAT_DONG',
            'phai_doimatkhau' => true,
            'ngaytao' => now(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'vaitro' => 'QUAN_TRI',
        ]);
    }

    public function doctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'vaitro' => 'BAC_SI',
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'vaitro' => 'NHAN_VIEN',
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (array $attributes) => [
            'trangthai' => 'KHOA',
        ]);
    }

    public function passwordConfirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'phai_doimatkhau' => false,
        ]);
    }
}