<?php

/*
 * Impostazioni del modulo HR (si leggono con config('humanresources.<chiave>')).
 */
return [
    'name' => 'HumanResources',

    /*
     | Domini delle mail istituzionali UNIPA. Sono obbligatorie per tutti tranne che per gli
     | studenti delle superiori (S4, S5), che dovranno cambiarla appena ne avranno una.
     */
    'institutional_domains' => ['community.unipa.it', 'unipa.it'],

    /*
     | Età minima per registrarsi (documento HR 2.2.1).
     */
    'minimum_age' => 16,

    /*
     | Inizio dell'anno accademico (mese-giorno, fuso italiano): in quel giorno tutti passano
     | all'anno successivo e per tutto ottobre viene chiesto di confermare l'anno frequentato.
     */
    'academic_year_start' => '10-01',

    /*
     | Pagina con l'informativa privacy da accettare in registrazione.
     | TODO: link definitivo dell'informativa dell'associazione.
     */
    'privacy_policy_url' => env('VIVERE_PRIVACY_POLICY_URL'),
];
