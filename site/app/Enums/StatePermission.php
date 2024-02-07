<?php

namespace App\Enums;

final class StatePermission
{
    const None = 0;

    const ManagersCanShowAndEditEditorsCanShow = 1;

    const EditorsCanShowAndEdit = 2; // "Private" State & default state

    const ManagersAndEditorsCanShowAndEdit = 3; // "Open" state

    const AllCanShowManagersAndEditorsCanEdit = 4; // "Public" state

    const AllCanShowManagersCanEdit = 5; // "Archived" state

    const ManagersCanShowAndEdit = 6;
}
