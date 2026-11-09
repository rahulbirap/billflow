<?php

it('reports the application is healthy', function () {
    $this->get('/up')->assertOk();
});
