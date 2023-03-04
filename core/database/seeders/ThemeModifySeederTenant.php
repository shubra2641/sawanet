<?php

namespace Database\Seeders;

use App\Models\StaticOption;
use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Plugins\PageBuilder\Addons\Landlord\Common\Themes;

class ThemeModifySeederTenant extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $themes = [
            'theme_one' => 'hexfashion',
            'theme_two' => 'furnito',
            'theme_three' => 'medicom'
        ];


        foreach ($themes as $index => $value)
        {
            $static_option = StaticOption::where('option_name','regexp',$index)->get();
            foreach ($static_option as $key => $item)
            {
                $option_name = str_replace($index, $value, $item->option_name);
                $item->option_name = $option_name;
                $item->save();
            }
        }
    }
}
