<?php

namespace App\Enums;

final class StatePermission
{
    const None = 0;

    const HoldersCanShowAndEdit = 1; // "Private" State & default state

    const ManagersAndHoldersCanShowAndEdit = 2; // "Open" state

    const AllCanShowManagersAndHoldersCanEdit = 3; // "Public" state

    const AllCanShowManagersCanEdit = 4; // "Archived" state

    const ManagersCanShowAndEdit = 5;
}
