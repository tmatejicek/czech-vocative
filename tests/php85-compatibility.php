<?php declare(strict_types=1);

error_reporting(E_ALL);
set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

require __DIR__ . '/../src/CzechName.php';

$converter = new Granam\CzechVocative\CzechName();
$assertions = 0;
$assertSame = static function ($expected, $actual) use (&$assertions): void {
    if ($expected !== $actual) {
        throw new RuntimeException('Vocative compatibility regression.');
    }
    $assertions++;
};

foreach ([
    ['man_first_name_tests', false, false],
    ['man_last_name_tests', false, true],
    ['woman_first_name_tests', true, false],
    ['woman_last_name_tests', true, true],
] as [$dataset, $isWoman, $isLastName]) {
    $lines = file(__DIR__ . '/CzechVocative/data/' . $dataset . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException('Missing upstream name fixtures.');
    }
    foreach ($lines as $line) {
        [$name, $expected] = explode(' ', rtrim($line), 2);
        $assertSame($expected, $converter->vocative($name, $isWoman, $isLastName));
        $assertSame($expected, $converter->vocative($name, null, $isLastName));
        $assertSame($expected, $converter->vocative($name, $isWoman));
        $assertSame($expected, $converter->vocative($name));
        $assertSame($converter->vocative($name), $converter->vocative($name, null, null));
        $assertSame(!$isWoman, $converter->isMale($name));
    }
}

$assertSame('Tome', $converter->vocative('toM'));
$assertSame('Androide', $converter->vocative('android'));
$assertSame('nokia?!', $converter->vocative('nokia?!'));
$assertSame('Siemensi', $converter->vocative("\n\t  siemens \n\t   "));

restore_error_handler();
echo 'OK: ' . $assertions . " assertions; no runtime diagnostics.\n";
