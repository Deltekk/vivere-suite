<?php

test('la home rimanda alla piattaforma HR', function () {
    $this->get('/')->assertRedirect('/hr');
});
