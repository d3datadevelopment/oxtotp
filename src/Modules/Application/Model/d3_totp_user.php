<?php
/**
 * Created by PhpStorm.
 * User: DanielSeifert
 * Date: 17.10.2018
 * Time: 12:03
 */

namespace D3\Totp\Modules\Application\Model;

class d3_totp_user extends d3_totp_user_parent
{
    public function d3UseTotp()
    {
        return false;
    }

    public function d3GetTotpSecret()
    {
        return false;
    }

    public function d3SetTotpSecret()
    {
        return false;
    }
}