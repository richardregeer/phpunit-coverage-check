<?php
// Inspired by: https://ocramius.github.io/blog/automated-code-coverage-check-for-github-pull-requests-with-travis/
if (!isset($argv[1]) || !file_exists($argv[1])) {
    echo 'Invalid input file provided' . PHP_EOL;
    exit(1);
}

if (!isset($argv[2])) {
    echo 'An integer checked percentage must be given as second parameter'. PHP_EOL;
    exit(1);
}

$inputFile = $argv[1];
$percentage = min(100, max(0, (int)$argv[2]));

$xml = new SimpleXMLElement(file_get_contents($inputFile));
$metrics = $xml->xpath('//metrics');

$elements = 0;
$coveredElements = 0;
$statements = 0;
$coveredstatements = 0;
$methods = 0;
$coveredmethods = 0;

foreach ($metrics as $metric) {
    $elements += (int)$metric['elements'];
    $coveredElements += (int)$metric['coveredelements'];
    $statements += (int)$metric['statements'];
    $coveredstatements += (int)$metric['coveredstatements'];
    $methods += (int)$metric['methods'];
    $coveredmethods += (int)$metric['coveredmethods'];
}

// See calculation: https://confluence.atlassian.com/pages/viewpage.action?pageId=79986990
$TPC = ($coveredstatements + $coveredmethods + $coveredElements) / ($statements + $methods + $elements) * 100;

if ($TPC < $percentage) {
    echo 'Total code coverage is ' . sprintf('%0.2f', $TPC) . '%, which is below the accepted ' . $percentage . '%' . PHP_EOL;
    exit(1);
}

echo 'Total code coverage is ' . sprintf('%0.2f', $TPC) . '% - OK!' . PHP_EOL;