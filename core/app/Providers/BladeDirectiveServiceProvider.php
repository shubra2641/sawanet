<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeDirectiveServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Blade::directive('isTenant',function (){
            return tenant();
        });

//        Blade::directive('themeInclude', function ($expression){
//            \Artisan::call('view:clear');
//            $real_path = str_replace(["'","."], ["","/"], $expression);
//            return $real_path;
////            $real_path = str_replace(["'","."], ["","/"], resource_path((include_theme_path($real_path))));
////            return $real_path;
////            echo include_theme_path($real_path);
////            return  Blade::include(include_theme_path($expression));
////            dd(include_theme_path(str_replace("'",'',$expression)));
////            return Blade::include(include_theme_path(str_replace("'",'',$expression)));
//        });
    }
}
