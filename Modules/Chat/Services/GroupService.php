<?php

namespace Modules\Chat\Services;

use Modules\Chat\Repositories\GroupRepository;

class GroupService
{
    protected $groupRepository;

    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function getAllGroup()
    {
        return $this->groupRepository->getAllGroup();
    }
}
