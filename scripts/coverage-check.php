<?php

/**
 * Check code coverage from clover.xml and enforce 100% requirement.
 */

$cloverFile = $argv[1] ?? 'build/logs/clover.xml';

if (!file_exists($cloverFile)) {
    echo "\033[41;97m Coverage file not found: $cloverFile \033[0m" . PHP_EOL;
    exit(1);
}

$xml = simplexml_load_file($cloverFile);
if ($xml === false) {
    echo "\033[41;97m Failed to parse coverage file \033[0m" . PHP_EOL;
    exit(1);
}

$metrics = $xml->project->metrics;
$statements = (int)$metrics['statements'];
$covered = (int)$metrics['coveredstatements'];

if ($statements === 0) {
    echo "\033[41;97m No statements found in coverage report \033[0m" . PHP_EOL;
    exit(1);
}

$coverage = ($covered / $statements) * 100;
$coverageDisplay = number_format($coverage, 2) . '%';

if ($coverage >= 100) {
    echo "\033[42;30m Coverage: $coverageDisplay \033[0m" . PHP_EOL;
    exit(0);
} else {
    echo "\033[41;97m Coverage: $coverageDisplay (100% required) \033[0m" . PHP_EOL;
    exit(1);
}
