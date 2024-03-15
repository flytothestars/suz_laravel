<?php

namespace App\Listeners\Ldap;

use Adldap\Laravel\Events\Synchronized;

class SynchronizedEventListener
{

    /**
     * Handle the event.
     *
     * @param Synchronized $event
     * @return void
     */
    public function handle(Synchronized $event)
    {
        $groups = $event->user->getGroups(['*'], true);
        $roles = [];

        foreach ($groups as $group) {
            $name = $group->cn;
            if ($name[0] == 'Администраторы_СУЗ') {
                $roles[] = 'администратор';
            } elseif ($name[0] == 'Диспетчера_СУЗ') {
                $roles[] = 'диспетчер';
            } elseif ($name[0] == 'Техники_СУЗ') {
                $roles[] = 'техник';
            } elseif ($name[0] == 'Кладовщики_СУЗ') {
                $roles[] = 'кладовщик';
            } elseif ($name[0] == 'Супервизоры_СУЗ') {
                $roles[] = 'супервизер';
            } elseif ($name[0] == 'Инспекторы_СУЗ') {
                $roles[] = 'инспектор';
            } elseif ($name[0] == 'Просмотр_Маршрута_СУЗ') {
                $roles[] = 'просмотр маршрута';
            } elseif ($name[0] == 'Просмотр_заявок_СУЗ') {
                $roles[] = 'просмотр заявок';
            }
        }

        $event->model->syncRoles($roles);
    }
}
