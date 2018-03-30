<?php
return [
    'js' => [
        /*
         * Js minification adapter
         *  old -  \Dvelum\App\Code\Minify\Adapter\JsMin
         *  new -  \Dvelum\App\Code\Minify\Adapter\MatthiasMullieMinify\Js
         */
        'adapter'=>'\\Dvelum\\App\\Code\\Minify\\Adapter\\MatthiasMullieMinify\\Js'
    ],
    'css' => [
        'adapter'=>'\\Dvelum\\App\\Code\\Minify\\Adapter\\MatthiasMullieMinify\\Css'
    ],
];