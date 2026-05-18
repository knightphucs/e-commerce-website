<?php

test('the home page redirects visitors to the shop', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('shop.index'));
});
