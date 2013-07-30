<?php

class A_Resource_Minifier_Routes extends Mixin
{
    function initialize()
    {
        $app = $this->create_app('/nextgen-static');
        $app->route('scripts', 'I_Resource_Manager#static_scripts');
        $app->route('styles',  'I_Resource_Manager#static_styles');
        $app = $this->create_app('/nextgen-dynamic');
        $app->route('scripts', 'I_Resource_Manager#dynamic_scripts');
        $app->route('styles',  'I_Resource_Manager#dynamic_styles');
    }
}