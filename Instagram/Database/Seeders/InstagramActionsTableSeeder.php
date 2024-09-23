<?php

namespace Modules\Instagram\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Instagram\Entities\Action;

class InstagramActionsTableSeeder extends Seeder
{
    public function run()
    {
        if (DB::table(Action::TABLE_NAME)->get()->count() == 0) {
            $actions = [
                //trigger
                [
                    'name' => 'New Media Posted in my Account',
                    'task' => 'new_media_posted_in_my_account',
                    'type' => 'new_media_posted_in_my_account',
                    'for_trigger' => true,
                    'for_action' => false,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ],
            ];

            DB::table(Action::TABLE_NAME)->insert($actions);
        }

        $triggers = DB::table(Action::TABLE_NAME)->where('for_trigger', true)->count();
        $actions = DB::table(Action::TABLE_NAME)->where('for_action', true)->count();

        DB::table('applications')->where('type', config('instagram.type'))->update(['has_triggers' => $triggers ? true : false, 'has_actions' => $actions ? true : false]);
    }
}
