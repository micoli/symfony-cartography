<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\User;

/**
 * @method User|null findOneByUsername(string $username)
 * @method User|null findOneByEmail(string $email)
 * @method User|null findOneBy(array $criteria)
 * @method User[] findBy(array $criteria,array $orders, ?int $maxResults)
 */
interface UserRepositoryInterface
{
}
