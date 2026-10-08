<?php

namespace App\Support;

/**
 * Palette ufficiale di Vivere, nel formato di Filament (11 sfumature OKLCH, da 50 a 950).
 *
 * Colori del brand: blu #071D99 (primario), viola #6200EE, giallo #FFCC33,
 * sfondo #F3F3FD, testo #1D1D1D.
 *
 * Le scale sono fatte a mano invece di usare Color::hex() perché Filament, partendo da un
 * esadecimale, conserva solo la tinta e usa luminosità fisse: il blu dei pulsanti sarebbe
 * venuto molto più chiaro di #071D99. Qui ogni colore del brand sta ESATTAMENTE nella
 * sfumatura che Filament usa di più:
 *  - 600 per primary e secondary (pulsanti, link, elementi attivi in modalità chiara);
 *  - 400 per warning (il giallo è chiaro: Filament ci mette sopra testo scuro da solo);
 *  - 50 e 950 per gray (sfondo della pagina e colore del testo in modalità chiara).
 *
 * Il contrasto tra testo e sfondo è verificato in tests/Unit/VivereColorsTest.php.
 * Le scale sono registrate in VivereSuitePanelProvider e valgono per tutti i panel.
 */
final class VivereColors
{
    /** Blu Vivere, colore principale: #071D99 = sfumatura 600. */
    public const Primary = [
        50 => 'oklch(0.970 0.014 265.237)',
        100 => 'oklch(0.932 0.035 265.237)',
        200 => 'oklch(0.868 0.070 265.237)',
        300 => 'oklch(0.770 0.120 265.237)',
        400 => 'oklch(0.640 0.175 265.237)',
        500 => 'oklch(0.480 0.205 265.237)',
        600 => 'oklch(0.336 0.195 265.237)',
        700 => 'oklch(0.290 0.165 265.237)',
        800 => 'oklch(0.250 0.135 265.237)',
        900 => 'oklch(0.215 0.105 265.237)',
        950 => 'oklch(0.165 0.075 265.237)',
    ];

    /** Viola Vivere, colore secondario: #6200EE = sfumatura 600. */
    public const Secondary = [
        50 => 'oklch(0.970 0.016 286.542)',
        100 => 'oklch(0.935 0.038 286.542)',
        200 => 'oklch(0.875 0.080 286.542)',
        300 => 'oklch(0.780 0.140 286.542)',
        400 => 'oklch(0.660 0.210 286.542)',
        500 => 'oklch(0.565 0.255 286.542)',
        600 => 'oklch(0.481 0.278 286.542)',
        700 => 'oklch(0.420 0.240 286.542)',
        800 => 'oklch(0.360 0.195 286.542)',
        900 => 'oklch(0.300 0.150 286.542)',
        950 => 'oklch(0.230 0.105 286.542)',
    ];

    /** Giallo Vivere, usato per gli avvisi: #FFCC33 = sfumatura 400. */
    public const Warning = [
        50 => 'oklch(0.985 0.030 88.684)',
        100 => 'oklch(0.967 0.065 88.684)',
        200 => 'oklch(0.940 0.110 88.684)',
        300 => 'oklch(0.905 0.150 88.684)',
        400 => 'oklch(0.868 0.165 88.684)',
        500 => 'oklch(0.790 0.160 88.684)',
        600 => 'oklch(0.680 0.140 88.684)',
        700 => 'oklch(0.560 0.115 88.684)',
        800 => 'oklch(0.460 0.090 88.684)',
        900 => 'oklch(0.390 0.070 88.684)',
        950 => 'oklch(0.280 0.050 88.684)',
    ];

    /**
     * Grigi leggermente tendenti al viola, presi dallo sfondo del brand:
     * #F3F3FD = 50 (sfondo pagina), #1D1D1D = 950 (testo; sfondo in modalità scura).
     */
    public const Gray = [
        50 => 'oklch(0.967 0.013 286.148)',
        100 => 'oklch(0.935 0.012 286.148)',
        200 => 'oklch(0.890 0.012 286.148)',
        300 => 'oklch(0.820 0.013 286.148)',
        400 => 'oklch(0.700 0.016 286.148)',
        500 => 'oklch(0.555 0.018 286.148)',
        600 => 'oklch(0.460 0.017 286.148)',
        700 => 'oklch(0.380 0.013 286.148)',
        800 => 'oklch(0.310 0.009 286.148)',
        900 => 'oklch(0.265 0.005 286.148)',
        950 => 'oklch(0.231 0 0)',
    ];
}
