<?php

namespace PHPSTORM_META {

    override(
        \Psr\Http\Message\ServerRequestInterface::getAttribute(),
        map([
            'session' => \VerteXVaaR\BlueAuth\Mvcr\Model\Session::class,
        ]),
    );
}
