<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Models\User::class, 1)->create()->each(function (\App\Models\User $user) {
            $user->score()->saveMany(factory(\App\Models\MemberScore::class, mt_rand(2, 8))->make());
            $member = $user->members()->where('type', 'wxlite')->first();
            $member->points= $user->score()->sum('score_change');
            $member->save();
        });
    }
}
