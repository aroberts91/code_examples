<?php

namespace App\Domain\User\Data;

final class UserCreateData
{
    /** @var string */
    public $title;

    /** @var string */
    public $first_name;

    /** @var string */
    public $surname;

    /** @var string */
    public $email;
}