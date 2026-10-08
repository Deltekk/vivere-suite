<?php

use App\Support\VivereColors;
use Filament\Support\Colors\Color;

/*
 * La palette di Filament deve contenere ESATTAMENTE i colori del brand nelle sfumature
 * più usate e garantire testi leggibili (WCAG AA: contrasto >= 4.5 per il testo normale).
 */

test('i colori del brand sono nelle sfumature previste', function (array $palette, int $shade, string $hex) {
    expect(Color::convertToHex($palette[$shade]))->toBe($hex);
})->with([
    'blu primario' => [VivereColors::Primary, 600, '#071d99'],
    'viola' => [VivereColors::Secondary, 600, '#6200ee'],
    'giallo' => [VivereColors::Warning, 400, '#ffcc33'],
    'sfondo' => [VivereColors::Gray, 50, '#f3f3fd'],
    'testo' => [VivereColors::Gray, 950, '#1d1d1d'],
]);

test('le combinazioni di testo e sfondo usate da Filament sono leggibili', function (string $text, string $background) {
    expect(Color::calculateContrastRatio($text, $background))->toBeGreaterThanOrEqual(4.5);
})->with([
    'testo sullo sfondo della pagina' => [VivereColors::Gray[950], VivereColors::Gray[50]],
    'testo secondario sulle card' => [VivereColors::Gray[500], 'oklch(1 0 0)'],
    'testo bianco sui pulsanti blu' => ['oklch(1 0 0)', VivereColors::Primary[600]],
    'testo bianco sui pulsanti blu (tema scuro)' => ['oklch(1 0 0)', VivereColors::Primary[500]],
    'testo bianco sui pulsanti viola' => ['oklch(1 0 0)', VivereColors::Secondary[600]],
    'link blu nel tema scuro' => [VivereColors::Primary[400], VivereColors::Gray[950]],
    'testo scuro sugli avvisi gialli' => [VivereColors::Gray[950], VivereColors::Warning[400]],
]);

test('ogni scala ha le 11 sfumature richieste da Filament', function (array $palette) {
    expect(array_keys($palette))->toBe([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950]);
})->with([
    'primary' => [VivereColors::Primary],
    'secondary' => [VivereColors::Secondary],
    'warning' => [VivereColors::Warning],
    'gray' => [VivereColors::Gray],
]);
