<?php

declare(strict_types=1);

// Inspired by: https://ocramius.github.io/blog/automated-code-coverage-check-for-github-pull-requests-with-travis/

const XPATH_METRICS = '//metrics';
const STATUS_OK = 0;
const STATUS_ERROR = 1;

function formatCoverage(float $number): string
{
    return sprintf('%0.2f %%', $number);
}

function loadMetrics(string $file): array
{
    $xml = new SimpleXMLElement(file_get_contents($file));

    return $xml->xpath(XPATH_METRICS);
}

function printStatus(string $msg, int $exitCode = STATUS_OK)
{
    echo $msg.PHP_EOL;
    exit($exitCode);
}

if (! isset($argv[1]) || ! file_exists($argv[1])) {
    printStatus("Invalid input file {$argv[1]} provided.", STATUS_ERROR);
}

if (! isset($argv[2])) {
    printStatus(
        'An integer checked percentage must be given as second parameter.',
        STATUS_ERROR
    );
}

$onlyEchoPercentage = isset($argv[3]) && $argv[3] === '--only-percentage';

$inputFile = $argv[1];
$percentage = min(100, max(0, (float) $argv[2]));

$elements = 0;
$coveredElements = 0;
$statements = 0;
$coveredstatements = 0;
$methods = 0;
$coveredmethods = 0;

foreach (loadMetrics($inputFile) as $metric) {
    $elements += (int) $metric['elements'];
    $coveredElements += (int) $metric['coveredelements'];
    $statements += (int) $metric['statements'];
    $coveredstatements += (int) $metric['coveredstatements'];
    $methods += (int) $metric['methods'];
    $coveredmethods += (int) $metric['coveredmethods'];
}

// See calculation: https://confluence.atlassian.com/pages/viewpage.action?pageId=79986990
$coveredMetrics = $coveredstatements + $coveredmethods + $coveredElements;
$totalMetrics = $statements + $methods + $elements;

if ($totalMetrics === 0) {
    printStatus('Insufficient data for calculation. Please add more code.', STATUS_ERROR);
}

$totalPercentageCoverage = $coveredMetrics / $totalMetrics * 100;

if ($totalPercentageCoverage < $percentage && ! $onlyEchoPercentage) {
    printStatus(
        'Total code coverage is '.formatCoverage($totalPercentageCoverage).' which is below the accepted '.$percentage.'%',
        STATUS_ERROR
    );
}

if ($totalPercentageCoverage < $percentage && $onlyEchoPercentage) {
    printStatus(formatCoverage($totalPercentageCoverage), STATUS_ERROR);
}

if ($onlyEchoPercentage) {
    printStatus(formatCoverage($totalPercentageCoverage));
}

printStatus('Total code coverage is '.formatCoverage($totalPercentageCoverage).' – OK!');
