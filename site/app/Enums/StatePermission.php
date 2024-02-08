<?php

namespace App\Enums;

final class StatePermission
{
    const None = 0;

    const ManagersCanShowAndEditHoldersCanShow = 1;

    const HoldersCanShowAndEdit = 2; // "Private" State & default state

    const ManagersAndHoldersCanShowAndEdit = 3; // "Open" state

    const AllCanShowManagersAndHoldersCanEdit = 4; // "Public" state

    const AllCanShowManagersCanEdit = 5; // "Archived" state

    const ManagersCanShowAndEdit = 6;
}
