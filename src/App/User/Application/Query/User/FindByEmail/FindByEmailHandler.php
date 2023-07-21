<?php

declare(strict_types=1);

namespace App\User\Application\Query\User\FindByEmail;

use App\Shared\Application\Query\Item;
use App\Shared\Application\Query\QueryHandlerInterface;
use App\User\Domain\Read\User;
use App\User\Domain\Repository\FindUserByEmailInterface;

final class FindByEmailHandler implements QueryHandlerInterface
{
    public function __construct(private readonly FindUserByEmailInterface $repository)
    {
    }

    public function __invoke(FindByEmailQuery $query): Item
    {
        $userView = $this->repository->oneByEmailAsArray($query->email);

        return Item::fromPayload($userView['uuid']->toString(), User::TYPE, $userView);
    }
}
