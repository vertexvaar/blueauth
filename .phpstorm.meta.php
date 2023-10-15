<?php

namespace PHPSTORM_META {

    use Psr\Http\Message\ServerRequestInterface;
    use VerteXVaaR\BlueAuth\Mvcr\Model\Session;

    override(
        ServerRequestInterface::getAttribute(),
        map([
            'session' => Session::class
        ])
    );
}
