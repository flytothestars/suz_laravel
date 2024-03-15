<?php

namespace App\Handlers;

use Adldap\Models\User as LdapUser;
use App\Models\User as EloquentUser;

class LdapAttributeHandler
{
    public function handle(LdapUser $ldapUser, EloquentUser $eloquentUser)
    {
        $eloquentUser->username = $ldapUser->getAccountName();
        $eloquentUser->name = $ldapUser->getCommonName();
    }
}
