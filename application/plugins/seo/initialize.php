<?php

// initialize seo
include("seo.php");

$seo = new SEO(array(
    "title" => "SwiftMVC Framework",
    "keywords" => "mvc, mvc framework, php, php framework" ,
    "description" => "Made with love in India",
    "author" => "Cloudstuff",
    "robots" => "INDEX,FOLLOW",
    "photo" => CDN . "img/logo.png"
));

Framework\Registry::set("seo", $seo);