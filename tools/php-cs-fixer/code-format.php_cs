<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([__DIR__ .  "/../../vendor"])
    ->in([__DIR__ .  "/../../"]);

$config = new PhpCsFixer\Config();

return $config->setRules([
    "@PSR12" => true,
    "strict_param" => false,
    "cast_spaces" => true,
    "concat_space" => ["spacing" => "one"],
    "return_type_declaration" => ["space_before" => "one"],
    "array_syntax" => ["syntax" => "short"],
    "function_typehint_space" => true
])
->setFinder($finder);